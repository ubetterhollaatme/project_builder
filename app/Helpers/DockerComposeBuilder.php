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

    public function __construct(array $config, array $parsedNodeBuildSample)
    {
        $this->config = array_merge($this->config, $config);
        $this->parsedNodeBuildSample = $parsedNodeBuildSample;
    }

    #[NoReturn] public function build(array $defaultServices = []): void
    {
        $build = [
            'version' => $this->config['version'],
            'services' => $defaultServices,
        ];

        $nodes = Node::all();

        foreach ($nodes as $i => $node) {
            foreach ($this->parsedNodeBuildSample['services'] as $serviceName => $service) {
                if (!$this->isNeedSerialNumberIn($serviceName)) {
                    continue;
                }

                $build['services']["{$serviceName}{$i}"] = $this->serviceKeyIdentifierRecursive($service, $i);
            }
        }

        yaml_emit_file('/var/www/html/project/build.yml', $build);

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
        try {
            if ($this->isNeedSerialNumberIn($value)) {
                preg_match('/node_/', $value, $matches);
                return str_replace($matches[0], "{$matches[0]}{$id}", $value);
            }
        } catch (\Throwable $e) {
            dd($value);
        }

        return $value;
    }

    function isNeedSerialNumberIn(string $str): bool
    {
        return str_starts_with($str, 'php_')
            || str_starts_with($str, 'mysql_');
    }
}
