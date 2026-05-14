<?php

declare(strict_types=1);

if (!defined('EVO_CORE_PATH')) {
    define('EVO_CORE_PATH', __DIR__ . '/../');
}

if (!defined('EVO_MANAGER_URL')) {
    define('EVO_MANAGER_URL', 'http://127.0.0.1/manager/');
}

if (!defined('EVO_SITE_URL')) {
    define('EVO_SITE_URL', 'http://127.0.0.1/');
}

if (!class_exists('DocumentParser')) {
    class DocumentParser
    {
        public function getConfig(string $key, mixed $default = null): mixed
        {
            return $default;
        }

        public function hasPermission(string $permission, string $context = ''): int
        {
            return 1;
        }

        /** @param array<string, mixed> $params */
        public function invokeEvent(string $eventName, array $params = []): mixed
        {
            return [];
        }

        public function clearCache(string $type = ''): void
        {
        }
    }
}

if (!function_exists('evo')) {
    function evo(): DocumentParser
    {
        static $evo = null;

        if (!$evo instanceof DocumentParser) {
            $evo = new DocumentParser();
        }

        return $evo;
    }
}
