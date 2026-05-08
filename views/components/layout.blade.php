@props([
    'vertical' => false,
    'horizontal' => false,
    'size' => 50,
    'collapsed' => false,
    'secondCollapsed' => false,
    'static' => false,
    'auto' => false,
    'sliderDisabled' => false,
    'removeSlider' => false,
    'overflow' => null,
    'layoutHeight' => '100%',
    'firstBackgroundColor' => null,
    'secondBackgroundColor' => null,
    'layoutKey' => null,
    'firstKey' => null,
    'secondKey' => null,
])

@php
    $type = $vertical ? 'vertical' : 'horizontal';
    $firstSide = $vertical ? 'top' : 'left';
    $secondSide = $vertical ? 'bottom' : 'right';
    $key = $layoutKey ?: 'evo-ui.' . $type . '.layout';
    $resolvedFirstKey = $firstKey ?: $key . '.' . $firstSide;
    $resolvedSecondKey = $secondKey ?: $key . '.' . $secondSide;
@endphp

<div
    {{ $attributes->class(['evo-ui-layout', 'evo-ui-layout--' . $type, 'is-static' => $static]) }}
    data-evo-layout
    data-role="layout"
    data-key="{{ $key }}"
    data-layout-type="{{ $type }}"
    data-size="{{ $size }}"
    data-static="{{ $static ? 'true' : 'false' }}"
    style="height: {{ $layoutHeight }};"
>
    <div
        class="evo-ui-layout__pane evo-ui-layout__pane--{{ $firstSide }}"
        data-role="layout-pane"
        data-key="{{ $resolvedFirstKey }}"
        data-side="{{ $firstSide }}"
        style="{{ $vertical ? 'height' : 'width' }}: {{ $static ? $size . 'px' : $size . '%' }}; {{ $firstBackgroundColor ? 'background-color:' . $firstBackgroundColor . ';' : '' }} {{ $collapsed ? 'display:none;' : '' }} {{ $overflow ? 'overflow:' . $overflow . ';' : '' }}"
    >
        {{ $first ?? '' }}
    </div>

    @if(!$removeSlider && !$collapsed && !$secondCollapsed && $size)
        <div
            class="evo-ui-layout__slider {{ $vertical ? 'evo-ui-layout__slider--height' : 'evo-ui-layout__slider--width' }} {{ ($sliderDisabled || $static) ? 'is-disabled' : '' }}"
            role="separator"
            aria-orientation="{{ $vertical ? 'horizontal' : 'vertical' }}"
            data-role="layout-slider"
            data-key="{{ $key }}.slider"
            data-evo-layout-slider
        >
            <x-evo::icon :name="$vertical ? 'dots-horizontal' : 'dots-vertical'" class="evo-ui-layout__slider-icon" />
        </div>
    @endif

    <div
        class="evo-ui-layout__pane evo-ui-layout__pane--{{ $secondSide }}"
        data-role="layout-pane"
        data-key="{{ $resolvedSecondKey }}"
        data-side="{{ $secondSide }}"
        style="{{ $secondBackgroundColor ? 'background-color:' . $secondBackgroundColor . ';' : '' }} {{ $secondCollapsed ? 'display:none;' : '' }}"
    >
        {{ $second ?? '' }}
    </div>
</div>
