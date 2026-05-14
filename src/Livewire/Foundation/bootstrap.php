<?php

require_once __DIR__ . '/Auth/Access/AuthorizesRequests.php';
require_once __DIR__ . '/Auth/Access/AuthorizationException.php';
require_once __DIR__ . '/Auth/Access/Response.php';
require_once __DIR__ . '/Http/Middleware/TrimStrings.php';
require_once __DIR__ . '/Http/Middleware/ConvertEmptyStringsToNull.php';
require_once __DIR__ . '/Http/Events/RequestHandled.php';

if (!function_exists('devo_ui_alias_type')) {
    function devo_ui_alias_type(string $alias, string ...$targets): void
    {
        $exists = class_exists($alias, false) || interface_exists($alias, false) || trait_exists($alias, false);

        if ($exists) {
            return;
        }

        foreach ($targets as $target) {
            if (class_exists($target) || interface_exists($target) || trait_exists($target)) {
                class_alias($target, $alias);
                return;
            }
        }
    }
}

devo_ui_alias_type(
    'Illuminate\\Foundation\\Auth\\Access\\AuthorizesRequests',
    EvoUI\Livewire\Foundation\Auth\Access\AuthorizesRequests::class
);
devo_ui_alias_type(
    'Illuminate\\Auth\\Access\\AuthorizationException',
    EvoUI\Livewire\Foundation\Auth\Access\AuthorizationException::class
);
devo_ui_alias_type(
    'Illuminate\\Auth\\Access\\Response',
    EvoUI\Livewire\Foundation\Auth\Access\Response::class
);
devo_ui_alias_type(
    'Illuminate\\Foundation\\Http\\Middleware\\TrimStrings',
    EvoUI\Livewire\Foundation\Http\Middleware\TrimStrings::class
);
devo_ui_alias_type(
    'Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull',
    EvoUI\Livewire\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class
);
devo_ui_alias_type(
    'Illuminate\\Foundation\\Http\\Events\\RequestHandled',
    EvoUI\Livewire\Foundation\Http\Events\RequestHandled::class
);
