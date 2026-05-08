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
    $type = (string) ($column['edit_type'] ?? 'text');
    $label = __((string) ($column['label'] ?? $key));
    $placeholder = !empty($column['placeholder']) ? __((string) $column['placeholder']) : $label;
    $disabled = $rowId < 1 || $field === '';

    $toText = static function (mixed $displayValue): string {
        if (is_array($displayValue)) {
            $label = data_get($displayValue, 'label');

            if (is_scalar($label)) {
                return (string) $label;
            }

            return collect($displayValue)
                ->filter(fn ($item) => is_scalar($item))
                ->map(fn ($item) => (string) $item)
                ->implode(', ');
        }

        return is_scalar($displayValue) || $displayValue === null ? (string) $displayValue : '';
    };

    $display = $toText($displayValue);
    $inputClass = trim('evo-ui-inline-edit__input ' . (string) ($column['input_class'] ?? ''));
@endphp

<span
    class="{{ trim('evo-ui-inline-edit ' . $class) }}"
    x-data="{
        original: '',
        focus(event) {
            this.original = event.target.value;
        },
        cancel(event) {
            event.target.value = this.original;
        }
    }"
>
    <input
        type="{{ $type === 'number' ? 'number' : 'text' }}"
        class="{{ $inputClass }}"
        value="{{ $display }}"
        title="{{ $label }}"
        aria-label="{{ $label }}"
        placeholder="{{ $placeholder }}"
        @disabled($disabled)
        @click.stop
        @dblclick.stop
        @focus="focus($event)"
        @keydown.enter.prevent="$event.target.blur()"
        @keydown.escape.prevent="cancel($event); $event.target.blur()"
        wire:change="updateInlineField({{ $rowId }}, '{{ $field }}', $event.target.value)"
    >
</span>
