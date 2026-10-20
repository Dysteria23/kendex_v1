@props([
    'title',
    'description' => 'Kendex ordena las solicitudes de gasto de tu empresa: aprobación por niveles, control de presupuesto y pagos con trazabilidad.',
    'image' => null,
    'noindex' => false,
    'canonical' => null,
])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }}</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit($description, 158, '') }}">
@if ($noindex)
    <meta name="robots" content="noindex, nofollow">
@endif
<link rel="canonical" href="{{ $canonical ?? url()->current() }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="Kendex">
<meta property="og:locale" content="es_DO">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit($description, 158, '') }}">
<meta property="og:url" content="{{ $canonical ?? url()->current() }}">
<meta property="og:image" content="{{ $image ?? asset('og-image.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Kendex: control de gastos con aprobaciones y presupuesto">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="#0e5f63">
@if (config('kendex.ga4_id'))
    <meta name="ga4-id" content="{{ config('kendex.ga4_id') }}">
@endif
