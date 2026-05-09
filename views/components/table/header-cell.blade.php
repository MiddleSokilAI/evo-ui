@props([
    'column',
    'sort' => '',
    'direction' => 'asc',
])

@php
    $columnKey = (string) ($column['key'] ?? '');
    $sortable = (bool) ($column['sortable'] ?? false);
    $headerActions = collect((array) ($column['header_actions'] ?? []))
        ->filter(fn ($action) => is_array($action) && trim((string) ($action['key'] ?? '')) !== '')
        ->values();
@endphp

<th @class([$column['class'] ?? null, 'is-sortable' => $sortable, 'has-header-actions' => $headerActions->isNotEmpty()])>
    @if($headerActions->isNotEmpty())
        <span class="evo-ui-table-header">
            @if($sortable)
                <button type="button" class="evo-ui-sort" wire:click="setSort('{{ $columnKey }}')">
                    <span>{{ __($column['label']) }}</span>
                    <span class="evo-ui-sort__icons" aria-hidden="true">
                        <x-evo::icon name="chevron-up" @class(['is-active' => $sort === $columnKey && $direction === 'asc']) />
                        <x-evo::icon name="chevron-down" @class(['is-active' => $sort === $columnKey && $direction === 'desc']) />
                    </span>
                </button>
            @else
                <span class="evo-ui-table-header__label">{{ __($column['label']) }}</span>
            @endif

            <span class="evo-ui-table-header__actions">
                @foreach($headerActions as $action)
                    @php
                        $actionKey = (string) ($action['key'] ?? '');
                        $actionIcon = (string) ($action['icon'] ?? 'settings');
                        $actionLabel = __((string) ($action['label'] ?? $actionKey));
                        $actionTone = (string) ($action['tone'] ?? 'default');
                    @endphp
                    <button
                        type="button"
                        @class([
                            'evo-ui-btn',
                            'evo-ui-btn--icon',
                            'evo-ui-btn--' . $actionTone => in_array($actionTone, ['primary', 'info', 'success', 'warning', 'danger'], true),
                        ])
                        title="{{ $actionLabel }}"
                        aria-label="{{ $actionLabel }}"
                        wire:click.stop="runHeaderAction(@js($columnKey), @js($actionKey))"
                        wire:target="runHeaderAction"
                        wire:loading.attr="disabled"
                        @click.stop
                        @dblclick.stop
                    >
                        <x-evo::icon :name="$actionIcon" class="evo-ui-btn__icon" />
                        <span class="evo-ui-sr-only">{{ $actionLabel }}</span>
                    </button>
                @endforeach
            </span>
        </span>
    @elseif($sortable)
        <button type="button" class="evo-ui-sort" wire:click="setSort('{{ $columnKey }}')">
            <span>{{ __($column['label']) }}</span>
            <span class="evo-ui-sort__icons" aria-hidden="true">
                <x-evo::icon name="chevron-up" @class(['is-active' => $sort === $columnKey && $direction === 'asc']) />
                <x-evo::icon name="chevron-down" @class(['is-active' => $sort === $columnKey && $direction === 'desc']) />
            </span>
        </button>
    @else
        {{ __($column['label']) }}
    @endif
</th>
