<?php

namespace EvoUI\Support;

use Illuminate\Database\Eloquent\Model;

class ResourceLayoutResolver
{
    public function __construct(
        protected FieldCatalog $catalog
    ) {
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function resolve(array $config, ?Model $resource = null): array
    {
        $config['sections'] = $this->normalizeSections($config['sections'] ?? []);

        if (($config['include_tvs'] ?? false) === true) {
            $config['sections'] = $this->appendTemplateVariables($config, $resource, $config['sections']);
        }

        return $config;
    }

    /**
     * @param array<int, array<string, mixed>> $sections
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeSections(array $sections): array
    {
        return collect($sections)
            ->map(function (array $section) {
                $section['fields'] = collect($section['fields'] ?? [])
                    ->map(fn (array $field) => $this->normalizeField($field))
                    ->values()
                    ->all();

                return $section;
            })
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $field
     * @return array<string, mixed>
     */
    protected function normalizeField(array $field): array
    {
        $catalog = $this->catalog->resourceFields()[$field['name'] ?? ''] ?? [];

        return array_replace_recursive($catalog, $field);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, array<string, mixed>> $sections
     * @return array<int, array<string, mixed>>
     */
    protected function appendTemplateVariables(array $config, ?Model $resource, array $sections): array
    {
        $templateId = (int) ($resource?->getAttribute('template') ?? data_get($config, 'source.template', 0));
        $fields = $this->catalog->templateVariableFields($templateId);

        if ($fields === []) {
            return $sections;
        }

        $sections[] = [
            'key' => 'template-variables',
            'tab' => (string) ($config['tv_tab'] ?? 'general'),
            'label' => (string) ($config['tv_section_label'] ?? 'evo::global.form_section_template_variables'),
            'icon' => 'variable',
            'fields' => $fields,
        ];

        return $sections;
    }
}
