<?php

namespace EvoUI\Support;

use RuntimeException;

class PhpConfigFile
{
    public function load(string $relativePath, array $fallback = []): array
    {
        $path = $this->path($relativePath);

        if (!is_file($path)) {
            return $fallback;
        }

        $config = require $path;

        return is_array($config) ? $config : $fallback;
    }

    public function write(string $relativePath, array $config): void
    {
        $path = $this->path($relativePath);
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, $this->directoryPermissions(), true);
        }

        $written = file_put_contents($path, "<?php\n\nreturn " . var_export($config, true) . ";\n");

        if ($written === false) {
            throw new RuntimeException("Unable to write config file [{$relativePath}].");
        }
    }

    protected function path(string $relativePath): string
    {
        if ($relativePath === '') {
            throw new RuntimeException('Config file path cannot be empty.');
        }

        return rtrim(EVO_CORE_PATH, '/') . '/' . ltrim($relativePath, '/');
    }

    protected function directoryPermissions(): int
    {
        $permissions = function_exists('evo') ? evo()->getConfig('new_folder_permissions', '0777') : '0777';

        return octdec((string) $permissions);
    }
}
