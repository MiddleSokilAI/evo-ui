@props([
    'filter',
    'labels' => [],
    'active' => false,
    'badge' => null,
    'activeTitle' => null,
])

@php($title = $activeTitle ?: ($labels === [] ? __($filter['label']) : implode(', ', $labels)))

<summary
    title="{{ $title }}"
    aria-label="{{ $title }}"
>
    <x-evo::icon :name="$filter['icon']" class="evo-ui-filter-icon" />
    @if($active)
        <span class="evo-ui-filter-badge" aria-hidden="true">{{ $badge }}</span>
    @endif
</summary>
