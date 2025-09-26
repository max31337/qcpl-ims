@props([
    'description' => '',
])

<p {{ $attributes->merge(['class' => 'text-sm text-muted-foreground']) }}>
    {{ $description ?: $slot }}
</p>