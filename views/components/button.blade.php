@props([
    'icon' => null,
    'label' => null,
    'active' => false,
    'disabled' => false,
    'href' => null,
    'iconOnly' => false,
    'tone' => 'neutral',
    'variant' => 'soft',
    'type' => 'button',
])

@php($classes = $attributes
    ->merge([
        'title' => $label,
        'aria-label' => $iconOnly && $label ? $label : null,
    ])
    ->class([
        'evo-ui-btn',
        'evo-ui-btn--icon' => $iconOnly,
        'evo-ui-btn--' . $tone => in_array($tone, ['primary', 'info', 'success', 'warning', 'danger'], true),
        'evo-ui-btn--filled' => $variant === 'filled',
        'is-active' => $active,
        'is-disabled' => $disabled,
    ]))

@if($href && !$disabled)
<a href="{{ $href }}" {{ $classes }}>
    @if($icon)
        <x-evo::icon :name="$icon" class="evo-ui-btn__icon" />
    @endif

    @if($label && !$iconOnly)
        <span class="evo-ui-btn__label">{{ $label }}</span>
    @elseif($label)
        <span class="evo-ui-sr-only">{{ $label }}</span>
    @else
        {{ $slot }}
    @endif
</a>
@else
<button type="{{ $type }}" @disabled($disabled) {{ $classes }}>
    @if($icon)
        <x-evo::icon :name="$icon" class="evo-ui-btn__icon" />
    @endif

    @if($label && !$iconOnly)
        <span class="evo-ui-btn__label">{{ $label }}</span>
    @elseif($label)
        <span class="evo-ui-sr-only">{{ $label }}</span>
    @else
        {{ $slot }}
    @endif
</button>
@endif
