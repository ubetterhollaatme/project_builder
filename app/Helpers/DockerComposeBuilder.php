<?php

namespace App\Helpers;

use App\Models\Node;
use JetBrains\PhpStorm\NoReturn;

class DockerComposeBuilder
{
    private array $config = [
        'version' => '3.7',
    ];
    private array $parsedNodeBuildSample;
    private array $build = [];

    public function __construct(array $config, array $parsedNodeBuildSample)
    {
        $this->config = array_merge($this->config, $config);
        $this->parsedNodeBuildSample = $parsedNodeBuildSample;
    }

    #[NoReturn] public function build(array $defaultServices = []): void
    {
        $this->build = [
            'version' => $this->config['version'],
            'services' => $defaultServices,
        ];
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
            if (!str_contains($serviceKey, 'php_')) {
                continue;
            }

            $volumes = [
                $service['volumes'][0]
            ];

            foreach ($nodes as $nodeKey => $node) {
                unset($nodes[$nodeKey]);

                $nodeId = $nodeKey + 1;
                $volumes[] = str_replace(
                    str_replace('php_', '', $serviceKey),
                    "node_{$nodeId}",
                    $service['volumes'][1]);

                if ($nodeId % $this->config['nodes_per_server'] === 0
                || !count($nodes)) {
                    $service['volumes'] = $volumes;
                    $this->build['services'][$serviceKey] = $service;
                    continue(2);
                }
            }
        }
dd($this->build);
        yaml_emit_file('/var/www/html/project/build.yml', $this->build);

        echo file_get_contents('/var/www/html/project/build.yml');
    }

    function serviceKeyIdentifierRecursive(array $service, int $id): array
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

    function serviceValueIdentifier(string $value, int $id): string
    {
        if ($this->isNeedSerialNumberIn($value)) {
            preg_match('/node_/', $value, $matches);

            return str_replace($matches[0], "{$matches[0]}{$id}", $value);
        }

        return $value;
    }

    function isNeedSerialNumberIn(string $str): bool
    {
        return str_starts_with($str, 'php_')
            || str_starts_with($str, 'mysql_')
            || str_contains($str, 'node_');
    }
}
