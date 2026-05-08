<?php

namespace EvoUI\Support;

use EvolutionCMS\Models\SiteTmplvar;

class FieldCatalog
{
    public function resourceFields(): array
    {
        return [
            'pagetitle' => ['type' => 'text', 'label' => 'evo::global.field_pagetitle', 'help' => 'evo::global.help_pagetitle'],
            'longtitle' => ['type' => 'text', 'label' => 'evo::global.field_longtitle', 'help' => 'evo::global.help_longtitle'],
            'description' => ['type' => 'text', 'label' => 'evo::global.field_description', 'help' => 'evo::global.help_description'],
            'alias' => ['type' => 'text', 'label' => 'evo::global.field_alias', 'help' => 'evo::global.help_alias'],
            'link_attributes' => ['type' => 'text', 'label' => 'evo::global.field_link_attributes', 'help' => 'evo::global.help_link_attributes'],
            'introtext' => ['type' => 'textarea', 'label' => 'evo::global.field_introtext', 'help' => 'evo::global.help_introtext'],
            'content' => ['type' => 'textarea', 'label' => 'evo::global.field_content', 'help' => 'evo::global.help_content'],
            'template' => ['type' => 'select', 'label' => 'evo::global.field_template', 'help' => 'evo::global.help_template'],
            'menutitle' => ['type' => 'text', 'label' => 'evo::global.field_menutitle', 'help' => 'evo::global.help_menutitle'],
            'menuindex' => ['type' => 'number', 'label' => 'evo::global.field_menuindex', 'help' => 'evo::global.help_menuindex'],
            'hidemenu' => ['type' => 'checkbox', 'label' => 'evo::global.field_show_in_menu', 'help' => 'evo::global.help_hidemenu', 'invert' => true],
            'parent' => ['type' => 'resource-parent', 'label' => 'evo::global.field_parent', 'help' => 'evo::global.help_parent'],
            'published' => ['type' => 'checkbox', 'label' => 'evo::global.field_published', 'help' => 'evo::global.help_published'],
            'pub_date' => ['type' => 'datetime', 'label' => 'evo::global.field_pub_date', 'help' => 'evo::global.help_pub_date'],
            'unpub_date' => ['type' => 'datetime', 'label' => 'evo::global.field_unpub_date', 'help' => 'evo::global.help_unpub_date'],
            'type' => ['type' => 'select', 'label' => 'evo::global.field_resource_type', 'help' => 'evo::global.help_resource_type'],
            'contentType' => ['type' => 'select', 'label' => 'evo::global.field_content_type', 'help' => 'evo::global.help_content_type'],
            'content_dispo' => ['type' => 'select', 'label' => 'evo::global.field_content_dispo', 'help' => 'evo::global.help_content_dispo'],
            'isfolder' => ['type' => 'checkbox', 'label' => 'evo::global.field_isfolder', 'help' => 'evo::global.help_isfolder'],
            'alias_visible' => ['type' => 'checkbox', 'label' => 'evo::global.field_alias_visible', 'help' => 'evo::global.help_alias_visible'],
            'richtext' => ['type' => 'checkbox', 'label' => 'evo::global.field_richtext', 'help' => 'evo::global.help_richtext'],
            'hide_from_tree' => ['type' => 'checkbox', 'label' => 'evo::global.field_show_children', 'help' => 'evo::global.help_hide_from_tree', 'invert' => true],
            'searchable' => ['type' => 'checkbox', 'label' => 'evo::global.field_searchable', 'help' => 'evo::global.help_searchable'],
            'cacheable' => ['type' => 'checkbox', 'label' => 'evo::global.field_cacheable', 'help' => 'evo::global.help_cacheable'],
            'privateweb' => ['type' => 'checkbox', 'label' => 'evo::global.field_privateweb', 'help' => 'evo::global.help_privateweb'],
            'privatemgr' => ['type' => 'checkbox', 'label' => 'evo::global.field_privatemgr', 'help' => 'evo::global.help_privatemgr'],
        ];
    }

    public function templateVariableFields(int $templateId): array
    {
        if ($templateId <= 0 || !class_exists(SiteTmplvar::class)) {
            return [];
        }

        return SiteTmplvar::query()
            ->select('site_tmplvars.*', 'site_tmplvar_templates.rank as template_rank')
            ->join('site_tmplvar_templates', 'site_tmplvar_templates.tmplvarid', '=', 'site_tmplvars.id')
            ->where('site_tmplvar_templates.templateid', $templateId)
            ->orderBy('site_tmplvar_templates.rank')
            ->orderBy('site_tmplvars.rank')
            ->orderBy('site_tmplvars.id')
            ->get()
            ->map(fn (SiteTmplvar $tv) => $this->templateVariableField($tv))
            ->values()
            ->all();
    }

    protected function templateVariableField(SiteTmplvar $tv): array
    {
        $type = $this->mapTvType((string) $tv->type);

        return array_filter([
            'name' => 'tvs.' . $tv->id,
            'type' => $type,
            'label' => $tv->caption ?: $tv->name,
            'description' => $tv->description ?: null,
            'default' => $this->defaultForType($type, $tv->default_text),
            'options' => $this->optionsForTv($tv),
            'rows' => in_array($type, ['textarea'], true) ? 4 : null,
            'span' => in_array($type, ['textarea'], true) ? 'full' : null,
            'storage' => [
                'type' => 'tv',
                'id' => (int) $tv->id,
                'name' => (string) $tv->name,
                'tv_type' => (string) $tv->type,
            ],
            'meta' => [
                'category' => (int) $tv->category,
                'display' => $tv->display,
                'display_params' => $tv->display_params,
                'editor_type' => (int) $tv->editor_type,
            ],
            'rules' => ['nullable'],
        ], fn ($value) => $value !== null && $value !== []);
    }

    protected function mapTvType(string $type): string
    {
        return match ($type) {
            'textarea', 'textareamini', 'richtext', 'htmlarea', 'custom_tv:multifields', 'multifields' => 'textarea',
            'dropdown', 'listbox' => 'select',
            'listbox-multiple', 'checkbox' => 'multi-checkbox',
            'option' => 'radio',
            'number' => 'number',
            'date' => 'datetime',
            default => 'text',
        };
    }

    protected function defaultForType(string $type, mixed $default): mixed
    {
        if ($type === 'multi-checkbox') {
            return $this->splitMultiValue((string) $default);
        }

        return $default;
    }

    protected function optionsForTv(SiteTmplvar $tv): array
    {
        if (!in_array($this->mapTvType((string) $tv->type), ['select', 'multi-checkbox', 'radio'], true)) {
            return [];
        }

        return collect(explode('||', (string) $tv->elements))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->map(function (string $option) {
                [$label, $value] = array_pad(explode('==', $option, 2), 2, null);
                $value ??= $label;

                return ['value' => $value, 'label' => $label];
            })
            ->values()
            ->all();
    }

    protected function splitMultiValue(string $value): array
    {
        return collect(explode('||', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
