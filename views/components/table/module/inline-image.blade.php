@props([
    'row',
    'column',
    'displayValue' => null,
    'class' => '',
])

@php
    $rowId = (int) data_get($row, 'id');
    $key = (string) ($column['key'] ?? '');
    $field = (string) ($column['edit_field'] ?? $key);
    $label = __((string) ($column['label'] ?? $key));
    $disabled = $rowId < 1 || $field === '';
    $image = is_array($displayValue) ? $displayValue : ['src' => $displayValue];
    $value = (string) data_get($row, $column['raw_value'] ?? ($field . '_raw'), data_get($image, 'path', data_get($image, 'src', '')));
    $inputId = 'evo-inline-image-' . preg_replace('/[^a-z0-9_-]/i', '-', $field . '-' . $rowId);
@endphp

<span class="{{ trim('evo-ui-inline-image ' . $class) }}">
    <input
        id="{{ $inputId }}"
        type="hidden"
        value="{{ $value }}"
        @disabled($disabled)
        wire:change="updateInlineField({{ $rowId }}, '{{ $field }}', $event.target.value)"
    >
    <button
        type="button"
        class="evo-ui-inline-image__button"
        title="{{ $label }}"
        aria-label="{{ $label }}"
        @disabled($disabled)
        @click.stop.prevent="EvoUI.browseImageField('{{ $inputId }}')"
        @dblclick.stop
    >
        <x-evo::table.image :image="$image" />
    </button>
</span>
