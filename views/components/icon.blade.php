@props([
    'name' => 'circle',
])

@php
    $icon = match (true) {
        str_starts_with($name, 'tabler-') => $name,
        str_starts_with($name, 'o-') => 'tabler-' . substr($name, 2),
        default => 'tabler-' . $name,
    };
    $bag = $attributes->merge(['aria-hidden' => 'true']);
    $class = trim((string) $bag->get('class', ''));
    $iconAttributes = $bag->getAttributes();

    unset($iconAttributes['class']);

    $svg = '';

    if (function_exists('svg')) {
        try {
            $svg = svg($icon, $class, $iconAttributes)->toHtml();
        } catch (\Throwable) {
            $svg = svg('tabler-circle', $class, $iconAttributes)->toHtml();
        }
    }
@endphp

{!! $svg !!}
