<?php

namespace EvoUI\Support;

class ResourceLayoutResolver
{
    public function __construct(
        protected FieldCatalog $catalog
    ) {
    }

    public function resolve(array $config, ?object $resource = null): array
    {
        $config['sections'] = $this->normalizeSections($config['sections'] ?? []);

        if (($config['include_tvs'] ?? false) === true) {
            $config['sections'] = $this->appendTemplateVariables($config, $resource, $config['sections']);
        }

        return $config;
    }

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

    protected function normalizeField(array $field): array
    {
        $catalog = $this->catalog->resourceFields()[$field['name'] ?? ''] ?? [];

        return array_replace_recursive($catalog, $field);
    }

    protected function appendTemplateVariables(array $config, ?object $resource, array $sections): array
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
