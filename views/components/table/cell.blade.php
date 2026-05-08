@props([
    'controller',
    'row',
    'column',
])

@php($customView = $controller->customCellView($column))

<td @class([$column['class'] ?? null])>
    @if($customView)
        @include($customView, ['controller' => $controller, 'row' => $row, 'column' => $column])
    @elseif(($column['cell'] ?? null) === 'resource-title')
        <div class="evo-ui-resource-title">
            <x-evo::icon :name="$row->isfolder ? 'folder' : 'file-text'" />
            <span>{{ $controller->cellDisplay($row, $column) }}</span>
        </div>
    @else
        {{ $controller->cellDisplay($row, $column) }}
    @endif
</td>
