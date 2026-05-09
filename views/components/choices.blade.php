@props([
    'options' => [],
    'selectedOptions' => [],
    'selectedValues' => [],
    'placeholder' => '',
    'searchPlaceholder' => null,
    'toggleMethod' => 'toggleChoice',
    'removeMethod' => 'removeChoice',
    'field' => null,
    'clearable' => true,
    'searchable' => true,
    'valueType' => 'string',
    'class' => '',
])

@php
    $options = collect((array) $options)->values();
    $selectedOptions = collect((array) $selectedOptions)->values();
    $selectedValues = array_map('strval', (array) $selectedValues);
    $placeholder = $placeholder !== '' ? $placeholder : __('evo::global.search_placeholder');
    $searchPlaceholder = $searchPlaceholder ?: $placeholder;

    $wireValue = static function (mixed $value) use ($valueType): string {
        return $valueType === 'int'
            ? (string) (int) $value
            : "'" . str_replace("'", "\\'", (string) $value) . "'";
    };

    $wireCall = static function (string $method, mixed $value) use ($field, $wireValue): string {
        $arguments = [];

        if ($field !== null && $field !== '') {
            $arguments[] = "'" . str_replace("'", "\\'", (string) $field) . "'";
        }

        $arguments[] = $wireValue($value);

        return $method . '(' . implode(', ', $arguments) . ')';
    };
@endphp

<div
    class="{{ trim('evo-ui-choices ' . $class) }}"
    x-data="{ open: false, search: '' }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
>
    <div
        class="evo-ui-choices__control"
        role="combobox"
        tabindex="0"
        x-on:click="open = !open"
        x-bind:aria-expanded="open ? 'true' : 'false'"
    >
        <span class="evo-ui-choices__chips">
            @forelse($selectedOptions as $option)
                @php
                    $optionValue = $option['value'] ?? '';
                @endphp
                <span class="evo-ui-choices__chip">
                    <span>{{ $option['label'] ?? $optionValue }}</span>
                    @if($clearable && empty($option['locked']))
                        <span
                            role="button"
                            tabindex="0"
                            class="evo-ui-choices__remove"
                            wire:click.stop="{{ $wireCall($removeMethod, $optionValue) }}"
                            x-on:click.stop
                        >
                            <x-evo::icon name="x" />
                        </span>
                    @endif
                </span>
            @empty
                <span class="evo-ui-choices__placeholder">{{ $placeholder }}</span>
            @endforelse
        </span>
    </div>

    <div class="evo-ui-choices__dropdown" x-show="open" x-cloak>
        @if($searchable)
            <input
                type="search"
                class="evo-ui-input evo-ui-choices__search"
                x-model.debounce.150ms="search"
                placeholder="{{ $searchPlaceholder }}"
                x-on:click.stop
            >
        @endif

        @foreach($options as $option)
            @php
                $value = $option['value'] ?? '';
                $label = (string) ($option['label'] ?? $value);
                $isSelected = in_array((string) $value, $selectedValues, true);
                $searchLabel = mb_strtolower($label);
            @endphp
            <button
                type="button"
                @class(['evo-ui-choices__option', 'is-selected' => $isSelected])
                @if($searchable)
                    x-show="search.trim() === '' || @js($searchLabel).includes(search.toLowerCase())"
                @endif
                wire:click.stop="{{ $wireCall($toggleMethod, $value) }}"
            >
                <span>{{ $label }}</span>
                @if($isSelected)
                    <x-evo::icon name="check" />
                @endif
            </button>
        @endforeach
    </div>
</div>
