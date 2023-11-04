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

    #[NoReturn] public function build(): void
    {
        $build = [
            'version' => $this->config['version'],
            'services' => [],
        ];


        echo "<pre>";
        print_r($this->parsedNodeBuildSample);

        $nodes = Node::all();

        dd($nodes);

        for ($i = 0; $i < $config->maternity_hospital_nodes; $i++) {
            foreach ($nodeSample['services'] as $serviceName => $service) {
                if (!$this->isNeedSerialNumberIn($serviceName)) {
                    continue;
                }

                $build['services']["{$serviceName}_{$i}"] = $this->serviceKeyIdentifierRecursive($service, $i);
            }
        }

        yaml_emit_file(__DIR__ . '/build.yml', $build);

        echo file_get_contents(__DIR__ . '/build.yml');
    }

    function serviceKeyIdentifierRecursive(array $service, int $id): array
    {
        $identifiedService = [];

        foreach ($service as $attribute => $value) {
            $key = $this->isNeedSerialNumberIn($attribute) ? "{$attribute}_{$id}" : $attribute;
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
            preg_match('/([a-z]|_[a-z])*/m', $value, $matches);
            print_r($matches);
            return str_replace($matches[0], "{$matches[0]}_{$id}", $value);
        }

        return $value;
    }

    function isNeedSerialNumberIn(string $str): bool
    {
        return str_starts_with($str, 'php_')
            || str_starts_with($str, 'mysql_');
    }
}
