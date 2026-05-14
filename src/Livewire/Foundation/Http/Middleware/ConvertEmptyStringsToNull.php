<?php

namespace EvoUI\Livewire\Foundation\Http\Middleware;

use Closure;

class ConvertEmptyStringsToNull
{
    /** @var list<callable> */
    protected static array $skipCallbacks = [];

    public static function skipWhen(callable $callback): void
    {
        static::$skipCallbacks[] = $callback;
    }

    public function handle(mixed $request, Closure $next): mixed
    {
        return $next($request);
    }

    public static function shouldSkip(mixed $request): bool
    {
        foreach (static::$skipCallbacks as $callback) {
            if ($callback($request)) {
                return true;
            }
        }

        return false;
    }
}
