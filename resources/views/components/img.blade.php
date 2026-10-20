{{-- Imagen accesible: el texto alternativo, el ancho y el alto son obligatorios (checklist 10 y 12). --}}
@props(['src', 'alt', 'width', 'height', 'lazy' => true])
<img src="{{ $src }}" alt="{{ $alt }}" width="{{ $width }}" height="{{ $height }}"
     @if ($lazy) loading="lazy" decoding="async" @endif {{ $attributes }}>
