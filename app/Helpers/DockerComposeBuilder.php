<?php

namespace App\Helpers;

use App\Models\Node;
use FilesystemIterator;
use JetBrains\PhpStorm\NoReturn;
use RecursiveDirectoryIterator;
use function PHPUnit\Framework\directoryExists;

class DockerComposeBuilder
{
    protected array $config;
    protected array $parsedNodeBuildSample;
    protected array $build;
    protected array $paths;

    public function __construct(array $config, array $build, array $parsedNodeBuildSample)
    {
        $this->config = $config;
        $this->build = $build;
        $this->parsedNodeBuildSample = $parsedNodeBuildSample;
//        TODO
//        $this->paths = [
//            'nginx_config' => '/var/www/html/project/docker/nginx/nginx.conf',
//            'nginx_config_full_sample' => '/var/www/html/project/docker/nginx/nginx.sample.conf',
//            'nginx_config_node_sample' => '/var/www/html/project/nodes/node_sample/docker/nginx/server.sample.conf',
//            'root_path' => "/var/www/html/project/nodes/node_{$nodeNumber}/public",
//        ];
    }

    #[NoReturn] public function build(array $defaultServices = []): void
    {
        $nodes = Node::all();
        $serversCount = round(count($nodes) / $this->config['nodes_per_server']);

        for ($i = 1; $i <= $serversCount; $i++) {
            foreach ($this->parsedNodeBuildSample['services'] as $serviceName => $service) {
                if (!$this->isNeedSerialNumberIn($serviceName)) {
                    continue;
                }

                $this->build['services']["{$serviceName}{$i}"] = $this->serviceKeyIdentifierRecursive($service, $i);
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
        yaml_emit_file('/var/www/html/project/build.yml', $this->build);

        echo file_get_contents('/var/www/html/project/build.yml');
    }

    protected function putNodeToNginxConf(string $serviceKey, int $nodeNumber): void
    {
        $expose = max(array_map(function ($port) {
            return (int)$port;
        }, $this->build['services']['nginx']['ports']));
        $expose++;
        $configPathServerSample = '/var/www/html/project/docker/nginx/nginx.sample.conf';
        $configPathServer = '/var/www/html/project/docker/nginx/nginx.conf';
        $configPathNode = '/var/www/html/project/nodes/node_sample/docker/nginx/server.sample.conf';
        $configSampleOfNode = file_get_contents($configPathNode);
        $configOfServer = file_get_contents($configPathServer);
        $toReplace = [
            'location_name' => $serviceKey,
            'root_path' => "/var/www/html/project/nodes/node_{$nodeNumber}/public",
            'listen_port' => $expose,
        ];
        $configOfNode = $configSampleOfNode;

        foreach ($toReplace as $directive => $value) {
            $configOfNode = str_replace("{{ {$directive} }}", $value, $configOfNode);
        }

        $this->build['services']['nginx']['ports'][] = $expose . ':' . $expose;
        file_put_contents(
            $configPathServer,
            substr($configOfServer,0,-2) . $configOfNode . '}' . PHP_EOL);
    }

    protected function compactNginxPorts(): void
    {
        $fPort = (int)$this->build['services']['nginx']['ports'][0];
        $lPort = (int)$this->build['services']['nginx']['ports'][0]
            + count($this->build['services']['nginx']['ports'])
            - 1;
        $this->build['services']['nginx']['ports'] = [
            "{$fPort}-{$lPort}:{$fPort}-{$lPort}"
        ];
    }

    protected function buildNodeDir(int $nodeNumber): void
    {
        $nodeSampleDir = '/var/www/html/project/nodes/node_sample';
        $nodeDir = "/var/www/html/project/nodes/node_{$nodeNumber}";

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

    protected function serviceKeyIdentifierRecursive(array $service, int $id): array
    {
        $identifiedService = [];

        foreach ($service as $attribute => $value) {
            $key = $this->isNeedSerialNumberIn($attribute) ? "{$attribute}{$id}" : $attribute;
            $identifiedService[$key] =
                is_array($value)
                    ? $this->serviceKeyIdentifierRecursive($value, $id)
                    : $this->serviceValueIdentifier($value, $id);
        }

        return $identifiedService;
    }

    protected function serviceValueIdentifier(string $value, int $id): string
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
