@props([
    'controller',
    'preset',
    'config',
    'rows',
    'selected' => [],
])

@php
    $columns = collect($config['columns'] ?? [])->keyBy('key');
    $list = $config['list'] ?? [];
    $titleColumn = $columns->get($list['title'] ?? 'pagetitle');
    $subtitleColumn = $columns->get($list['subtitle'] ?? null);
    $metaColumns = collect($list['meta'] ?? [])
        ->map(fn ($key) => $columns->get($key))
        ->filter()
        ->values();
@endphp

<div class="evo-ui-list">
    @forelse($rows as $row)
        @php($isSelected = in_array($row->id, $selected, true))
        <article
            wire:key="{{ $preset }}-list-row-{{ $row->id }}"
            wire:click="selectRow({{ $row->id }})"
            tabindex="0"
            aria-selected="{{ $isSelected ? 'true' : 'false' }}"
            @class(['evo-ui-list-item', 'is-selected' => $isSelected])
        >
            <div class="evo-ui-list-item__media">
                <x-evo::icon :name="$row->isfolder ? 'folder' : 'file-text'" />
            </div>

            <div class="evo-ui-list-item__body">
                <div class="evo-ui-list-item__main">
                    <strong>{{ $titleColumn ? $controller->cellDisplay($row, $titleColumn) : $row->id }}</strong>
                    @if($subtitleColumn)
                        <span>{{ $controller->cellDisplay($row, $subtitleColumn) }}</span>
                    @endif
                </div>

                @if($metaColumns->isNotEmpty())
                    <dl class="evo-ui-list-item__meta">
                        @foreach($metaColumns as $column)
                            @php($value = $controller->cellDisplay($row, $column))
                            @continue($value === '' || $value === '-')
                            @php($metaIcon = ($column['cell'] ?? null) === 'date' ? 'calendar' : ($column['meta_icon'] ?? null))

                            <div>
                                @if($metaIcon)
                                    <dt class="evo-ui-sr-only">{{ __($column['label']) }}</dt>
                                    <dd>
                                        <x-evo::icon :name="$metaIcon" />
                                        <span>{{ $value }}</span>
                                    </dd>
                                @else
                                    <dt>{{ __($column['label']) }}</dt>
                                    <dd>{{ $value }}</dd>
                                @endif
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>

            @if(!empty($config['row_actions']))
                <div class="evo-ui-row-actions">
                    @foreach($config['row_actions'] as $action)
                        <a
                            href="{{ $controller->actionHref($action, $row) }}"
                            title="{{ __($action['label']) }}"
                            aria-label="{{ __($action['label']) }}"
                            @class(['evo-ui-row-action--' . ($action['tone'] ?? '') => in_array($action['tone'] ?? '', ['primary', 'info', 'success', 'warning', 'danger'], true)])
                            @click.stop
                        >
                            <x-evo::icon :name="$action['icon']" />
                        </a>
                    @endforeach
                </div>
            @endif
        </article>
    @empty
        <div class="evo-ui-table-empty">
            @lang('evo::global.table_empty')
        </div>
    @endforelse
</div>
