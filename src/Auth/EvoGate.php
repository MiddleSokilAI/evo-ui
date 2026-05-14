<?php

namespace EvoUI\Auth;

use EvoUI\Support\ManagerContext;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Gate;

class EvoGate implements Gate
{
    /** @var array<string, callable|string> */
    protected array $abilities = [];
    /** @var list<callable> */
    protected array $before = [];
    /** @var list<callable> */
    protected array $after = [];
    protected mixed $user = null;

    public function has($ability)
    {
        return isset($this->abilities[$this->abilityName($ability)]);
    }

    public function define($ability, $callback)
    {
        $this->abilities[$this->abilityName($ability)] = $callback;
        return $this;
    }

    /**
     * @param array<string, string>|null $abilities
     */
    public function resource($name, $class, ?array $abilities = null)
    {
        return $this;
    }

    public function policy($class, $policy)
    {
        return $this;
    }

    public function before(callable $callback)
    {
        $this->before[] = $callback;
        return $this;
    }

    public function after(callable $callback)
    {
        $this->after[] = $callback;
        return $this;
    }

    /**
     * @param string|\UnitEnum|iterable<int, string|\UnitEnum> $ability
     */
    public function allows($ability, $arguments = [])
    {
        return $this->check($ability, $arguments);
    }

    /**
     * @param string|\UnitEnum|iterable<int, string|\UnitEnum> $ability
     */
    public function denies($ability, $arguments = [])
    {
        return !$this->check($ability, $arguments);
    }

    /**
     * @param string|\UnitEnum|iterable<int, string|\UnitEnum> $abilities
     */
    public function check($abilities, $arguments = [])
    {
        foreach ((array) $abilities as $ability) {
            if (!$this->raw($this->abilityName($ability), $arguments)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|\UnitEnum|iterable<int, string|\UnitEnum> $abilities
     */
    public function any($abilities, $arguments = [])
    {
        foreach ((array) $abilities as $ability) {
            if ($this->raw($this->abilityName($ability), $arguments)) {
                return true;
            }
        }

        return false;
    }

    public function authorize($ability, $arguments = [])
    {
        if (!$this->raw($this->abilityName($ability), $arguments)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('This action is unauthorized.');
        }

        return Response::allow();
    }

    public function inspect($ability, $arguments = [])
    {
        return $this->raw($this->abilityName($ability), $arguments)
            ? Response::allow()
            : Response::deny('This action is unauthorized.');
    }

    public function raw($ability, $arguments = [])
    {
        $ability = $this->abilityName($ability);
        $arguments = (array) $arguments;

        foreach ($this->before as $callback) {
            $result = $callback($this->user, $ability, $arguments);

            if ($result !== null) {
                return (bool) $result;
            }
        }

        $result = $this->resolve($ability, $arguments);

        foreach ($this->after as $callback) {
            $callback($this->user, $ability, $result, $arguments);
        }

        return (bool) $result;
    }

    public function getPolicyFor($class)
    {
        return null;
    }

    public function forUser($user)
    {
        $clone = clone $this;
        $clone->user = $user;

        return $clone;
    }

    /**
     * @return array<string, callable|string>
     */
    public function abilities()
    {
        return $this->abilities;
    }

    /**
     * @param array<int|string, mixed> $arguments
     */
    protected function resolve(string $ability, array $arguments): bool
    {
        if (isset($this->abilities[$ability])) {
            return (bool) app()->call($this->abilities[$ability], ['user' => $this->user, 'arguments' => $arguments]);
        }

        $manager = app(ManagerContext::class);

        if (str_starts_with($ability, 'role:')) {
            return $manager->role() === (int) substr($ability, 5);
        }

        return $manager->can($ability);
    }

    protected function abilityName(string|\UnitEnum $ability): string
    {
        return $ability instanceof \UnitEnum ? $ability->name : (string) $ability;
    }
}
