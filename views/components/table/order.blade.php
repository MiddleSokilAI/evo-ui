@props([
    'controller',
    'sort' => '',
    'direction' => 'asc',
])

@php($columns = $controller->sortableColumns())

@if($columns !== [])
    <details class="evo-ui-order">
        <summary title="@lang('evo::global.order')" aria-label="@lang('evo::global.order')">
            <x-evo::icon name="arrows-sort" />
        </summary>

        <div class="evo-ui-order-menu">
            @foreach($columns as $column)
                @php($active = $sort === ($column['key'] ?? null))
                <button
                    type="button"
                    wire:key="order-{{ $column['key'] }}"
                    wire:click="setSort('{{ $column['key'] }}')"
                    @class(['is-active' => $active])
                >
                    <span>{{ __($column['label']) }}</span>
                    @if($active)
                        <x-evo::icon :name="$direction === 'desc' ? 'sort-descending' : 'sort-ascending'" />
                    @endif
                </button>
            @endforeach
        </div>
    </details>
@endif
