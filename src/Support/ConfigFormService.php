<?php

namespace EvoUI\Support;

use Closure;

class ConfigFormService
{
    public function __construct(
        protected PhpConfigFile $files
    ) {
    }

    public function fill(array $config, array $fields): array
    {
        $data = [];
        $source = $this->source($config);
        $values = $this->files->load(
            (string) ($source['file'] ?? ''),
            config((string) ($source['root'] ?? 'evo-ui'), [])
        );

        foreach ($fields as $field) {
            data_set($data, $field['name'], data_get($values, $field['name'], $field['default'] ?? null));
        }

        return $data;
    }

    public function save(array $config, array $fields, array $data, Closure $storageValue): void
    {
        $source = $this->source($config);
        $root = (string) ($source['root'] ?? 'evo-ui');
        $values = $this->files->load((string) ($source['file'] ?? ''), config($root, []));

        foreach ($fields as $field) {
            data_set($values, $field['name'], $storageValue($field, data_get($data, $field['name'])));
        }

        $this->files->write((string) ($source['file'] ?? ''), $values);
        config()->set($root, $values);

        if (function_exists('evo')) {
            evo()->clearCache('full');
        }
    }

    protected function source(array $config): array
    {
        return (array) data_get($config, 'source', []);
    }
}
