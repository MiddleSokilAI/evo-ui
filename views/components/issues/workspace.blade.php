@props([
    'preset',
    'provider' => null,
    'context' => [],
    'wireKey' => null,
])

@php
    $livewireKey = $wireKey ?? $attributes->get('wire:key') ?? $attributes->get('key') ?? $preset . '-issue-workspace';
@endphp

<livewire:evo-ui.issue-workspace :preset="$preset" :provider="$provider" :context="$context" :key="$livewireKey" />
