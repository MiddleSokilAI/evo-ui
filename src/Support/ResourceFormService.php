<?php

namespace EvoUI\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;

class ResourceFormService
{
    public function __construct(
        protected TvValueRepository $tvs,
        protected LanguageBridge $languages
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public function modelInstance(array $config, int $recordId = 0): Model
    {
        $model = (string) data_get($config, 'source.model');
        $key = $this->sourceKey($config);
        $id = $recordId ?: (int) data_get($config, 'source.default', 0);
        $record = $id > 0 ? $model::query()->where($key, $id)->first() : null;

        $instance = $record ?: new $model();

        if (!$instance instanceof Model) {
            throw new \RuntimeException("Configured resource form model [{$model}] must extend Eloquent Model.");
        }

        return $instance;
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, mixed>
     */
    public function fill(array $config, array $fields, int $recordId, ?string $locale, Closure $displayValue): array
    {
        $data = [];
        $model = $this->modelInstance($config, $recordId);
        $resourceId = (int) $model->getAttribute($this->sourceKey($config));
        $translations = $this->languages->enabled($config)
            ? $this->languages->resourceValues($resourceId, $locale)
            : [];

        foreach ($fields as $field) {
            $value = $this->fieldValue($config, $model, $field, $translations, $locale);
            data_set($data, $field['name'], $displayValue($field, $value));
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, mixed> $data
     */
    public function save(array $config, array $fields, array $data, int $recordId, ?string $locale, Closure $storageValue): int
    {
        $model = $this->modelInstance($config, $recordId);
        $baseTvFields = [];
        $translatedFields = [];
        $translatedTvFields = [];

        foreach ($fields as $field) {
            if (($field['save'] ?? true) === false) {
                continue;
            }

            $value = $storageValue($field, data_get($data, $field['name']));

            if ($this->isTvField($field)) {
                if ($this->storesTranslatedTv($config, $field, $locale)) {
                    $translatedTvFields[$this->tvId($field)] = $value;
                } else {
                    $baseTvFields[$this->tvId($field)] = $value;
                }

                continue;
            }

            if ($this->storesTranslatedResourceField($config, $field, $locale)) {
                $translatedFields[$field['name']] = $value;
                continue;
            }

            $model->setAttribute($field['name'], $value);
        }

        $model->save();
        $resourceId = (int) $model->getAttribute($this->sourceKey($config));

        foreach ($baseTvFields as $tvId => $value) {
            $this->tvs->save($resourceId, (int) $tvId, $value);
        }

        foreach ($translatedTvFields as $tvId => $value) {
            $this->languages->saveTemplateVariableValue($resourceId, (int) $tvId, $locale, $value);
        }

        $this->languages->saveResourceValues($resourceId, $locale, $translatedFields);

        return $resourceId;
    }

    /** @param array<string, mixed> $config */
    public function sourceKey(array $config): string
    {
        return (string) data_get($config, 'source.key', 'id');
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $field
     * @param array<string, mixed> $translations
     */
    protected function fieldValue(array $config, Model $model, array $field, array $translations, ?string $locale): mixed
    {
        if ($this->isTvField($field)) {
            return $this->tvFieldValue($config, $model, $field, $locale);
        }

        if ($this->storesTranslatedResourceField($config, $field, $locale)) {
            return $translations[$field['name']] ?? $model->getAttribute($field['name']) ?? ($field['default'] ?? null);
        }

        return $model->getAttribute($field['name']) ?? ($field['default'] ?? null);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $field
     */
    protected function tvFieldValue(array $config, Model $model, array $field, ?string $locale): mixed
    {
        $resourceId = (int) $model->getAttribute($this->sourceKey($config));
        $tvId = $this->tvId($field);
        $default = $field['default'] ?? null;

        if ($this->storesTranslatedTv($config, $field, $locale)) {
            return $this->languages->templateVariableValue($resourceId, $tvId, $locale, $default);
        }

        return $this->tvs->value($resourceId, $tvId, $default);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $field
     */
    protected function storesTranslatedResourceField(array $config, array $field, ?string $locale): bool
    {
        return $this->languages->canStoreTranslations($config)
            && !$this->languages->isDefault($locale)
            && $this->languages->isResourceField($config, $field);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $field
     */
    protected function storesTranslatedTv(array $config, array $field, ?string $locale): bool
    {
        return $this->languages->canStoreTranslations($config)
            && !$this->languages->isDefault($locale)
            && $this->languages->isTemplateVariable($config, $field);
    }

    /** @param array<string, mixed> $field */
    protected function isTvField(array $field): bool
    {
        return data_get($field, 'storage.type') === 'tv';
    }

    /** @param array<string, mixed> $field */
    protected function tvId(array $field): int
    {
        return (int) data_get($field, 'storage.id');
    }
}
