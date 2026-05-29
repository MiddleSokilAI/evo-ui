<?php

namespace EvoUI\Livewire\Foundation\Testing\Concerns;

use Illuminate\Http\Request;

trait MakesHttpRequests
{
    /** @var array<string, mixed> */
    protected array $serverVariables = [];

    public function withoutMiddleware(mixed $middleware = null): static
    {
        if (function_exists('app')) {
            app()->instance('middleware.disable', true);
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     */
    public function call(
        string $method,
        string $uri,
        array $parameters = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null
    ): mixed {
        $request = Request::create(
            $uri,
            $method,
            $parameters,
            $cookies,
            $files,
            array_replace($this->serverVariables, $server),
            $content
        );

        return app()->handle($request);
    }

    /**
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    protected function transformHeadersToServerVars(array $headers): array
    {
        return collect($headers)
            ->mapWithKeys(function (mixed $value, string $name): array {
                $name = strtr(strtoupper($name), '-', '_');

                if (!in_array($name, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                    $name = 'HTTP_' . $name;
                }

                return [$name => $value];
            })
            ->all();
    }
}
