<?php

namespace EvoUI\Support;

use EvolutionCMS\Models\SiteTmplvarContentvalue;

class TvValueRepository
{
    public function value(int $contentId, int $tvId, mixed $default = null): mixed
    {
        if ($contentId <= 0 || $tvId <= 0 || !class_exists(SiteTmplvarContentvalue::class)) {
            return $default;
        }

        $value = SiteTmplvarContentvalue::query()
            ->where('contentid', $contentId)
            ->where('tmplvarid', $tvId)
            ->value('value');

        return $value === null || $value === '' ? $default : $value;
    }

    public function save(int $contentId, int $tvId, mixed $value): void
    {
        if ($contentId <= 0 || $tvId <= 0 || !class_exists(SiteTmplvarContentvalue::class)) {
            return;
        }

        SiteTmplvarContentvalue::query()->updateOrCreate(
            ['contentid' => $contentId, 'tmplvarid' => $tvId],
            ['value' => $this->stringValue($value)]
        );
    }

    protected function stringValue(mixed $value): string
    {
        if (is_array($value)) {
            return implode('||', array_map('strval', $value));
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return is_scalar($value) ? (string) $value : '';
    }
}
