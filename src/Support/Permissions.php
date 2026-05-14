<?php

namespace EvoUI\Support;

class Permissions
{
    public function __construct(protected ManagerContext $manager)
    {
    }

    /** @param array<string, mixed> $definition */
    public function allows(array $definition): bool
    {
        if (!$this->matchesRoles($definition)) {
            return false;
        }

        $all = array_values(array_filter([
            ...$this->list($definition['permissions'] ?? []),
            ...$this->list($definition['permission'] ?? []),
        ]));

        foreach ($all as $permission) {
            if (!$this->manager->can($permission)) {
                return false;
            }
        }

        $any = $this->list($definition['any_permission'] ?? []);

        return $any === [] || collect($any)->contains(fn (string $permission) => $this->manager->can($permission));
    }

    /** @param array<string, mixed> $definition */
    protected function matchesRoles(array $definition): bool
    {
        $roles = [
            ...$this->list($definition['roles'] ?? []),
            ...$this->list($definition['role'] ?? []),
        ];

        if ($roles === []) {
            return true;
        }

        $role = $this->manager->role();

        return $role !== null && in_array((string) $role, array_map('strval', $roles), true);
    }

    /** @return array<int, string> */
    protected function list(mixed $value): array
    {
        return collect((array) $value)
            ->map(fn ($item) => is_scalar($item) ? trim((string) $item) : '')
            ->filter()
            ->values()
            ->all();
    }
}
