@props([
    'image' => [],
])

@php
    $image = is_array($image) ? $image : ['src' => $image];
    $src = $image['src'] ?? '';
    $alt = $image['alt'] ?? '';
@endphp

<figure class="evo-ui-table-image">
    @if($src)
        <img src="{{ $src }}" alt="{{ $alt }}" width="38" height="28" loading="lazy" decoding="async">
    @else
        <x-evo::icon name="photo" />
    @endif
</figure>
