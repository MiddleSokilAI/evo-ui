@props([
    'column',
    'sort' => '',
    'direction' => 'asc',
])

<th @class([$column['class'] ?? null, 'is-sortable' => $column['sortable'] ?? false])>
    @if($column['sortable'] ?? false)
        <button type="button" class="evo-ui-sort" wire:click="setSort('{{ $column['key'] }}')">
            <span>{{ __($column['label']) }}</span>
            <span class="evo-ui-sort__icons" aria-hidden="true">
                <x-evo::icon name="chevron-up" @class(['is-active' => $sort === $column['key'] && $direction === 'asc']) />
                <x-evo::icon name="chevron-down" @class(['is-active' => $sort === $column['key'] && $direction === 'desc']) />
            </span>
        </button>
    @else
        {{ __($column['label']) }}
    @endif
</th>
