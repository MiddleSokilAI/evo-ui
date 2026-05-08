<?php

namespace EvoUI\Livewire\Foundation\Http;

use Illuminate\Contracts\Http\Kernel as KernelContract;

class Kernel implements KernelContract
{
    protected array $middleware = [];

    public function bootstrap(): void
    {
    }

    public function handle($request)
    {
        return $request;
    }

    public function terminate($request, $response): void
    {
    }

    public function getApplication()
    {
        return app();
    }

    public function hasMiddleware(string $middleware): bool
    {
        return in_array($middleware, $this->middleware, true);
    }

    public function pushMiddleware(string $middleware): void
    {
        if (!$this->hasMiddleware($middleware)) {
            $this->middleware[] = $middleware;
        }
    }
}
