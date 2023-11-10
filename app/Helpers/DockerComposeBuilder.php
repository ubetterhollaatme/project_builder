<?php

namespace App\Helpers;

use App\Models\Node;
use FilesystemIterator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\NoReturn;
use RecursiveDirectoryIterator;
use function PHPUnit\Framework\directoryExists;

class DockerComposeBuilder
{
    protected array $config;
    protected array $parsedNodeBuildSample;
    protected array $build;
    protected array $paths = ['base' => '/var/www/html/project'];

    public function __construct(array $config, array $build, array $parsedNodeBuildSample)
    {
        $this->config = $config;
        $this->build = $build;
        $this->parsedNodeBuildSample = $parsedNodeBuildSample;
        $this->paths = array_merge($this->paths, [
            'build' => "{$this->paths['base']}/build.yml",
            'nodes' => "{$this->paths['base']}/nodes",
            'nginx_config' => "{$this->paths['base']}/docker/nginx/nginx.conf",
            'nginx_config_sample_full' => "{$this->paths['base']}/docker/nginx/nginx.sample.conf",
            'nginx_config_sample_node' => "{$this->paths['base']}/nodes/node_sample/docker/nginx/server.sample.conf",
            'node_root' => '/public',
            'node_prefix' => '/node_',
        ]);
    }

    #[NoReturn] public function build(array $defaultServices = []): void
    {
        $nodes = Node::all();
        $serversCount = round(count($nodes) / $this->config['nodes_per_server']);

        $this->refreshNginxConf();
        $this->refreshNodesFolder();

        for ($i = 1; $i <= $serversCount; $i++) {
            foreach ($this->parsedNodeBuildSample['services'] as $serviceName => $service) {
                if (!$this->isNeedSerialNumberIn($serviceName)) {
                    continue;
                }

                $this->build['services']["{$serviceName}{$i}"] = $this->identifyServiceKeyRecursive($service, $i);
            }
        }
        foreach ($this->build['services'] as $serviceKey => $service) {
            if (!str_contains($serviceKey, 'php_') || str_contains($serviceKey, 'humanzepola')) {
                continue;
            }

            $volumes = [
                $service['volumes'][0]
            ];

            foreach ($nodes as $nodeNumber => $node) {
                unset($nodes[$nodeNumber]);

                $nodeId = $nodeNumber + 1;
                $volumes[] = str_replace(
                    str_replace('php_', '', $serviceKey),
                    "node_{$nodeId}",
                    $service['volumes'][1]);

                $this->putNodeToNginxConf($serviceKey, $nodeId);
                $this->buildNodeDir($nodeId);

                if ($nodeId % $this->config['nodes_per_server'] === 0
                    || !count($nodes)) {
                    $service['volumes'] = $volumes;
                    $this->build['services'][$serviceKey] = $service;
                    continue(2);
                }
            }
        }
        $this->compactNginxPorts();

        dd($this->build);

        yaml_emit_file($this->paths['build'], $this->build);

        echo file_get_contents($this->paths['build']);
    }

    protected function refreshNginxConf(): void
    {
        file_put_contents($this->paths['nginx_config'], file_get_contents($this->paths['nginx_config_sample_full']));
    }

    protected function refreshNodesFolder(): void
    {
        $fs = new Filesystem;

        foreach ($fs->directories($this->paths['nodes']) as $path) {
            if (str_contains($path, 'node_sample')) {
                continue;
            }

            $fs->deleteDirectory($path);
        }
    }

    protected function putNodeToNginxConf(string $serviceKey, int $nodeNumber): void
    {
        $expose = max(array_map(function ($port) {
            return (int)$port;
        }, $this->build['services']['nginx']['ports']));
        $configOfNode = file_get_contents($this->paths['nginx_config_sample_node']);
        $configOfServer = file_get_contents($this->paths['nginx_config']);
        $toReplace = [
            'location_name' => $serviceKey,
            'listen_port' => ++$expose,
            'root_path' => $this->paths['nodes']
                . $this->paths['node_prefix']
                . $nodeNumber
                . $this->paths['node_root'],
        ];

        foreach ($toReplace as $directive => $value) {
            $configOfNode = str_replace("{{ {$directive} }}", $value, $configOfNode);
        }

        $this->build['services']['nginx']['ports'][] = $expose . ':' . $expose;
        file_put_contents(
            $this->paths['nginx_config'],
            substr($configOfServer,0,-2) . $configOfNode . '}' . PHP_EOL);
    }

    protected function compactNginxPorts(): void
    {
        $portFirst = (int)$this->build['services']['nginx']['ports'][0];
        $portLast = (int)$this->build['services']['nginx']['ports'][0]
            + count($this->build['services']['nginx']['ports'])
            - 1;
        $this->build['services']['nginx']['ports'] = [
            "{$portFirst}-{$portLast}:{$portFirst}-{$portLast}"
        ];
    }

    protected function buildNodeDir(int $nodeNumber): void
    {
        $nodeSampleDir = "{$this->paths['nodes']}/node_sample";
        $nodeDir = $this->paths['nodes'] . $this->paths['node_prefix'] . $nodeNumber;

        if (!file_exists($nodeDir)) {
            mkdir($nodeDir, 0755);
        }

        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($nodeSampleDir, FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if (file_exists($nodeDir . DIRECTORY_SEPARATOR . $iterator->getSubPathname())) {
                continue;
            }
            if ($item->isDir()) {
                mkdir($nodeDir . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
            } else {
                copy($item, $nodeDir . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
            }
        }
    }

    protected function identifyServiceKeyRecursive(array $service, int $id): array
    {
        $identifiedService = [];

        foreach ($service as $attribute => $value) {
            $key = $this->isNeedSerialNumberIn($attribute) ? "{$attribute}{$id}" : $attribute;
            $identifiedService[$key] =
                is_array($value)
                    ? $this->identifyServiceKeyRecursive($value, $id)
                    : $this->identifyServiceValue($value, $id);
        }

        return $identifiedService;
    }

    protected function identifyServiceValue(string $value, int $id): string
    {
        if ($this->isNeedSerialNumberIn($value)) {
            preg_match('/node_/', $value, $matches);

            return str_replace($matches[0], "{$matches[0]}{$id}", $value);
        }

        return $value;
    }

    protected function isNeedSerialNumberIn(string $str): bool
    {
        return str_starts_with($str, 'php_')
            || str_starts_with($str, 'mysql_')
            || str_contains($str, 'node_');
    }
}
