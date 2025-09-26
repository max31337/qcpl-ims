@props([
    'variant' => 'default',
])

@php
$variants = [
    'default' => 'bg-primary/10 text-primary border-transparent',
    'secondary' => 'bg-secondary text-secondary-foreground border-transparent',
    'destructive' => 'bg-destructive/10 text-destructive border-transparent',
    'success' => 'bg-green-100 text-green-800 border-transparent',
    'warning' => 'bg-yellow-100 text-yellow-800 border-transparent',
    'outline' => 'text-foreground border-border',
];

$variantClasses = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 ' . $variantClasses]) }}>
    {{ $slot }}
</div>