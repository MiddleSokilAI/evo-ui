@props([
    'controller',
    'preset',
    'config',
    'row',
    'selected' => false,
])

<tr
    wire:key="{{ $preset }}-row-{{ $row->id }}"
    wire:click="selectRow({{ $row->id }})"
    tabindex="0"
    aria-selected="{{ $selected ? 'true' : 'false' }}"
    @class(['is-selected' => $selected, 'is-selectable' => $config['selectable'] ?? true])
>
    @foreach($config['columns'] ?? [] as $column)
        <x-evo::table.cell :controller="$controller" :row="$row" :column="$column" />
    @endforeach

    @if(!empty($config['row_actions']))
        <td class="evo-ui-row-actions">
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
        </td>
    @endif
</tr>
