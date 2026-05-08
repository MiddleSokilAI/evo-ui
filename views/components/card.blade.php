@props([
    'label' => null,
    'icon' => null,
])

<section {{ $attributes->class('evo-ui-card') }}>
    @if($label)
        <header class="evo-ui-card__header">
            @if($icon)
                <x-evo::icon :name="$icon" />
            @endif
            <h3>{{ __($label) }}</h3>
        </header>
    @endif

    {{ $slot }}
</section>
