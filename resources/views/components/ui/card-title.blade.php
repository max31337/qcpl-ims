@props([
    'text' => '',
])

<h3 {{ $attributes->merge(['class' => 'text-2xl font-semibold leading-none tracking-tight']) }}>
    {{ $text ?: $slot }}
</h3>