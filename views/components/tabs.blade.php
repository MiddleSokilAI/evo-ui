@props([
    'variant' => 'lift',
    'label' => null,
    'selected' => null,
])

@php
    $classes = ['evo-ui-tabs'];

    if ($variant) {
        $classes[] = 'tabs-' . $variant;
    }
@endphp

<div
    x-data="{
        tabs: [],
        selected: @if($selected) @js($selected) @else @entangle($attributes->wire('model')) @endif
    }"
    {{ $attributes
        ->except(['wire:model', 'wire:model.live'])
        ->class($classes)
        ->merge(['aria-label' => $label])
    }}
>
    <div class="tabs evo-ui-tab-labels" role="tablist">
        <template x-for="tab in tabs" :key="tab.name">
            <a
                href="#"
                role="tab"
                class="tab"
                x-html="tab.label"
                @click.prevent="tab.disabled ? null : selected = tab.name"
                :aria-selected="selected === tab.name"
                :class="{ 'tab-active': selected === tab.name, 'tab-disabled': tab.disabled, 'hidden': tab.hidden }"
            ></a>
        </template>
    </div>

    <div class="evo-ui-tab-panels">
        {{ $slot }}
    </div>
</div>
