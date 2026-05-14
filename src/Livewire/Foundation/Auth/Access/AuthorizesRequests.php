<?php

namespace EvoUI\Livewire\Foundation\Auth\Access;

use Illuminate\Contracts\Auth\Access\Gate;

trait AuthorizesRequests
{
    /**
     * @param array<int|string, mixed>|mixed $arguments
     */
    public function authorize(string|\UnitEnum $ability, mixed $arguments = []): mixed
    {
        return $this->getGate()->authorize($ability, $arguments);
    }

    /**
     * @param array<int|string, mixed>|mixed $arguments
     */
    public function authorizeForUser(mixed $user, string|\UnitEnum $ability, mixed $arguments = []): mixed
    {
        return $this->getGate()->forUser($user)->authorize($ability, $arguments);
    }

    protected function getGate(): Gate
    {
        if (function_exists('app')) {
            return app(Gate::class);
        }

        throw new \RuntimeException('Gate contract is not bound.');
    }
}

final class AuthorizesRequestsShim
{
    use AuthorizesRequests;
}
