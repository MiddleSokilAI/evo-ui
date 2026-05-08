@props([
    'value' => null,
    'label' => null,
    'color' => null,
    'icon' => null,
])

@php
    $legacyClass = '';

    if (is_array($value)) {
        $label ??= data_get($value, 'label');
        $color ??= data_get($value, 'color');
        $icon ??= data_get($value, 'icon');
        $legacyClass = (string) data_get($value, 'class', '');
    }

    if ($label === null) {
        $label = is_scalar($value) || $value === null ? (string) $value : '';
    }

    $label = is_scalar($label) || $label === null ? (string) $label : '';
    $color = is_scalar($color) || $color === null ? (string) $color : '';
    $icon = is_scalar($icon) || $icon === null ? (string) $icon : '';
    $isHex = preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1;

    if (!$isHex && $legacyClass === '' && $color !== '') {
        $legacyClass = $color;
    }

    $legacyClass = trim(preg_replace('/[^A-Za-z0-9_:\\-\\s]/', '', $legacyClass) ?? '');
    $badgeClass = trim('evo-ui-badge ' . ($isHex ? 'evo-ui-badge--dynamic ' : '') . $legacyClass);
    $badgeStyle = $isHex ? '--evo-ui-badge-color: ' . strtoupper($color) . ';' : null;
@endphp

<span {{ $attributes->merge(['class' => $badgeClass]) }} @if($badgeStyle) style="{{ $badgeStyle }}" @endif>
    @if($icon !== '')
        <x-evo::icon :name="$icon" />
    @endif
    <span>{{ $label }}</span>
</span>
