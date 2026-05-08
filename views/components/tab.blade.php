@props([
    'name' => null,
    'icon' => null,
    'label' => null,
    'disabled' => false,
    'hidden' => false,
])

@php
    $name ??= (string) \Illuminate\Support\Str::of($label ?: 'tab')->slug('-');
    $iconHtml = $icon
        ? \Illuminate\Support\Facades\Blade::render('<x-evo::icon name="' . e($icon) . '" class="tab-icon" />')
        : '';
    $labelHtml = '<span class="evo-ui-tab-label">' . $iconHtml . '<span>' . e($label) . '</span></span>';
@endphp

<span
    hidden
    data-name="{{ $name }}"
    x-init="
        const item = {
            name: @js($name),
            label: @js($labelHtml),
            disabled: @js($disabled),
            hidden: @js($hidden)
        };
        const index = tabs.findIndex((tab) => tab.name === item.name);
        index === -1 ? tabs.push(item) : tabs[index] = item;
    "
></span>

<div
    x-cloak
    x-show="selected === @js($name)"
    role="tabpanel"
    data-evo-tab-panel="{{ $name }}"
    class="tab-content"
    wire:key="evo-tab-panel-{{ $name }}"
>
    {{ $slot }}
</div>
