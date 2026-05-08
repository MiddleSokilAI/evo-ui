@props([
    'tabs' => [],
    'label' => null,
])

<nav
    {{ $attributes
        ->class('evo-ui-nav-tabs evo-ui-tab-labels tabs-lift')
        ->merge(['aria-label' => $label])
    }}
>
    <div class="evo-ui-nav-tabs__list" role="tablist">
        @foreach($tabs as $tab)
            @php
                $active = (bool) ($tab['active'] ?? false);
                $data = $tab['data'] ?? [];
                $type = $tab['type'] ?? 'link';
            @endphp

            @if($type === 'wire')
                <button
                    type="button"
                    role="tab"
                    @class(['tab evo-ui-nav-tab', 'tab-active is-active' => $active])
                    aria-selected="{{ $active ? 'true' : 'false' }}"
                    wire:click="{{ $tab['method'] ?? 'switchTab' }}('{{ $tab['argument'] ?? $tab['key'] ?? '' }}')"
                    @foreach($data as $name => $value)
                        @if($value === true || $value === '')
                            {{ $name }}
                        @elseif($value !== false && $value !== null)
                            {{ $name }}="{{ $value }}"
                        @endif
                    @endforeach
                >
                    <span class="evo-ui-nav-tab__label">
                        @if(!empty($tab['icon']))
                            <x-evo::icon :name="$tab['icon']" class="evo-ui-nav-tab__icon" />
                        @endif
                        <span>{!! $tab['label'] ?? '' !!}</span>
                    </span>
                </button>
            @else
                <a
                    href="{{ $tab['href'] ?? '#' }}"
                    role="tab"
                    @class(['tab evo-ui-nav-tab', 'tab-active is-active' => $active])
                    aria-selected="{{ $active ? 'true' : 'false' }}"
                    @foreach($data as $name => $value)
                        @if($value === true || $value === '')
                            {{ $name }}
                        @elseif($value !== false && $value !== null)
                            {{ $name }}="{{ $value }}"
                        @endif
                    @endforeach
                >
                    <span class="evo-ui-nav-tab__label">
                        @if(!empty($tab['icon']))
                            <x-evo::icon :name="$tab['icon']" class="evo-ui-nav-tab__icon" />
                        @endif
                        <span>{!! $tab['label'] ?? '' !!}</span>
                    </span>
                </a>
            @endif
        @endforeach
    </div>
</nav>
