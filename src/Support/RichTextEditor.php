<?php

namespace EvoUI\Support;

class RichTextEditor
{
    /**
     * @param string|array<int, string> $ids
     * @param array<string, array<string, mixed>> $options
     */
    public static function html(
        string|array $ids,
        string $height = '500px',
        string $editor = '',
        array $options = [],
        string $contentType = 'htmlmixed',
        ?string $theme = null,
    ): string {
        $elements = self::elements($ids);

        if ($elements === []) {
            return '';
        }

        $editor = self::configuredEditor($editor);
        $editorOptions = [];

        foreach ($elements as $element) {
            $editorOptions[$element] = array_replace(
                (array) ($options[$element] ?? []),
                $theme !== null && $theme !== '' && $editor === 'TinyMCE5' ? ['theme' => $theme] : [],
            );
        }

        $editorHtml = evo()->invokeEvent('OnRichTextEditorInit', [
            'editor' => $editor,
            'elements' => $elements,
            'height' => $height,
            'contentType' => $contentType,
            'options' => $editorOptions,
        ]);

        return is_array($editorHtml) ? implode('', $editorHtml) : '';
    }

    public static function configuredEditor(string $editor = '', string $fallback = 'eTinyMCE'): string
    {
        $editor = trim($editor);

        if ($editor === '' || $editor === 'system') {
            return (string) evo()->getConfig('which_editor', $fallback);
        }

        return $editor;
    }

    /** @return array<int, string> */
    public static function registered(): array
    {
        $registered = evo()->invokeEvent('OnRichTextEditorRegister');

        return collect(is_array($registered) ? $registered : [])
            ->flatten()
            ->filter(fn ($editor) => is_string($editor) && trim($editor) !== '')
            ->map(fn (string $editor) => trim($editor))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param string|array<int, string> $ids
     * @return array<int, string>
     */
    protected static function elements(string|array $ids): array
    {
        return collect(is_array($ids) ? $ids : explode(',', $ids))
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
