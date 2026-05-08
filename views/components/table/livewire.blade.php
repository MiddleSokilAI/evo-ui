@props([
    'preset',
    'context' => [],
    'wireKey' => null,
])

@php
    $livewireKey = $wireKey ?? $attributes->get('wire:key') ?? $attributes->get('key') ?? $preset;
@endphp

<livewire:evo-ui.module-table :preset="$preset" :context="$context" :key="$livewireKey" />
