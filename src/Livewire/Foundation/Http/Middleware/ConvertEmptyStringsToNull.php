<?php

namespace EvoUI\Livewire\Foundation\Http\Middleware;

use Closure;

class ConvertEmptyStringsToNull
{
    protected static array $skipCallbacks = [];

    public static function skipWhen(callable $callback): void
    {
        static::$skipCallbacks[] = $callback;
    }

    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public static function shouldSkip($request): bool
    {
        foreach (static::$skipCallbacks as $callback) {
            if ($callback($request)) {
                return true;
            }
        }

        return false;
    }
}
