@props([
    'tabs' => [],
    'label' => null,
])

<nav
    {{ $attributes->class('evo-ui-module-tabs')->merge(['aria-label' => $label, 'data-evo-module-tabs' => true]) }}
>
    <button type="button" class="evo-ui-module-tabs__arrow" data-evo-module-tabs-prev aria-label="@lang('evo::global.previous')">
        <x-evo::icon name="chevron-left" />
    </button>

    <div class="evo-ui-module-tabs__scroller" data-evo-module-tabs-scroller>
        @foreach($tabs as $tab)
            @php
                $data = $tab['data'] ?? [];
                $active = (bool) ($tab['active'] ?? false);
            @endphp
            <a
                href="{{ $tab['href'] ?? '#' }}"
                @class(['evo-ui-module-tab', 'is-active' => $active])
                @foreach($data as $name => $value)
                    {{ $name }}="{{ $value }}"
                @endforeach
            >
                @if(!empty($tab['icon']))
                    <x-evo::icon :name="$tab['icon']" class="evo-ui-module-tab__icon" />
                @endif
                <span>{!! $tab['label'] ?? '' !!}</span>
            </a>
        @endforeach
    </div>

    <button type="button" class="evo-ui-module-tabs__arrow" data-evo-module-tabs-next aria-label="@lang('evo::global.next')">
        <x-evo::icon name="chevron-right" />
    </button>
</nav>
