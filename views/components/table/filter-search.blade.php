@props([
    'filter',
])

<input
    type="search"
    class="evo-ui-input"
    placeholder="{{ __($filter['search_label'] ?? $filter['label']) }}"
    x-model.debounce.150ms="search"
    @keydown.enter.prevent="apply"
>
