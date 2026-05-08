<?php

namespace EvoUI\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LanguageBridge
{
    public function enabled(array $config = []): bool
    {
        $mode = data_get($config, 'multilingual.enabled', config('evo-ui.multilingual.enabled', 'auto'));

        if ($mode === false || $mode === 'off') {
            return false;
        }

        if ($mode === true || $mode === 'on') {
            return true;
        }

        return $this->hasSLangTables();
    }

    public function canStoreTranslations(array $config = []): bool
    {
        return $this->enabled($config) && $this->hasSLangTables();
    }

    public function locale(?string $locale = null): string
    {
        $locale = trim((string) $locale);

        return $locale !== '' ? $locale : $this->defaultLocale();
    }

    public function defaultLocale(): string
    {
        return $this->evoConfig('s_lang_default')
            ?: $this->evoConfig('manager_language')
            ?: 'uk';
    }

    public function locales(array $config = []): array
    {
        $configured = data_get($config, 'multilingual.locales', config('evo-ui.multilingual.locales', []));
        $locales = is_array($configured) ? $configured : $this->csv((string) $configured);

        if ($locales === []) {
            $locales = $this->csvConfig('s_lang_config');
        }

        if ($locales === []) {
            $locales = [$this->defaultLocale()];
        }

        return collect($locales)
            ->prepend($this->defaultLocale())
            ->map(fn ($locale) => strtolower(trim((string) $locale)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function isDefault(?string $locale): bool
    {
        return $this->locale($locale) === $this->defaultLocale();
    }

    public function resourceFields(array $config): array
    {
        $fields = data_get($config, 'multilingual.fields', config('evo-ui.multilingual.resource_fields', []));

        if (is_array($fields) && $fields !== []) {
            return $fields;
        }

        return ['pagetitle', 'longtitle', 'description', 'introtext', 'content', 'menutitle', 'seotitle', 'seodescription'];
    }

    public function isResourceField(array $config, array $field): bool
    {
        return in_array((string) ($field['name'] ?? ''), $this->resourceFields($config), true);
    }

    public function isTemplateVariable(array $config, array $field): bool
    {
        $id = (int) data_get($field, 'storage.id');

        return $id > 0 && in_array($id, $this->templateVariableIds($config), true);
    }

    public function resourceValues(int $resourceId, ?string $locale): array
    {
        if ($resourceId <= 0 || $this->isDefault($locale) || !$this->hasSLangTables()) {
            return [];
        }

        $row = DB::table('s_lang_content')
            ->where('resource', $resourceId)
            ->where('lang', $this->locale($locale))
            ->first();

        return $row ? (array) $row : [];
    }

    public function saveResourceValues(int $resourceId, ?string $locale, array $values): void
    {
        if ($resourceId <= 0 || $values === [] || $this->isDefault($locale) || !$this->hasSLangTables()) {
            return;
        }

        DB::table('s_lang_content')->updateOrInsert(
            ['resource' => $resourceId, 'lang' => $this->locale($locale)],
            array_merge($values, ['updated_at' => now()])
        );
    }

    public function templateVariableValue(int $resourceId, int $tvId, ?string $locale, mixed $default = null): mixed
    {
        if ($resourceId <= 0 || $tvId <= 0 || $this->isDefault($locale) || !$this->hasSLangTables()) {
            return $default;
        }

        $value = DB::table('s_lang_tmplvar_contentvalues')
            ->where('contentid', $resourceId)
            ->where('tmplvarid', $tvId)
            ->where('lang', $this->locale($locale))
            ->value('value');

        return $value === null || $value === '' ? $default : $value;
    }

    public function saveTemplateVariableValue(int $resourceId, int $tvId, ?string $locale, mixed $value): void
    {
        if ($resourceId <= 0 || $tvId <= 0 || $this->isDefault($locale) || !$this->hasSLangTables()) {
            return;
        }

        DB::table('s_lang_tmplvar_contentvalues')->updateOrInsert(
            ['contentid' => $resourceId, 'tmplvarid' => $tvId, 'lang' => $this->locale($locale)],
            ['value' => $this->stringValue($value)]
        );
    }

    public function templateVariableIds(array $config = []): array
    {
        $configured = data_get($config, 'multilingual.tvs', config('evo-ui.multilingual.tvs', []));
        $ids = is_array($configured) ? $configured : $this->csv((string) $configured);

        if ($ids === []) {
            $ids = $this->csvConfig('s_lang_tvs');
        }

        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function hasSLangTables(): bool
    {
        return Schema::hasTable('s_lang_content') && Schema::hasTable('s_lang_tmplvar_contentvalues');
    }

    protected function evoConfig(string $key, mixed $default = ''): mixed
    {
        return function_exists('evo') ? evo()->getConfig($key, $default) : $default;
    }

    protected function csvConfig(string $key): array
    {
        return $this->csv((string) $this->evoConfig($key, ''));
    }

    protected function csv(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
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
