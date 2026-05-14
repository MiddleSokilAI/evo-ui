<?php

namespace EvoUI\Contracts;

interface ModuleTableProvider
{
    public function total(): int;

    /** @return list<array<string, mixed>> */
    public function rows(int $page, int $perPage): array;

    /** @return list<array<string, mixed>> */
    public function filterGroups(): array;

    /**
     * @param array<string, mixed> $data
     */
    public function saveModal(array $data, ?int $recordId = null, string $mode = 'create'): mixed;
}
