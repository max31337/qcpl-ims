@props([
  'variant' => 'default', // default | success | destructive | warning | info
  'icon' => null,
])

@php
$variants = [
  'default' => 'border-border bg-card text-card-foreground',
  'success' => 'border-green-200 bg-green-50 text-green-800',
  'destructive' => 'border-red-200 bg-red-50 text-red-800',
  'warning' => 'border-yellow-200 bg-yellow-50 text-yellow-800',
  'info' => 'border-blue-200 bg-blue-50 text-blue-800',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border p-4 text-sm '.$variants[$variant]]) }}>
  <div class="flex items-start gap-3">
    @if($icon)
      @if($icon instanceof \Illuminate\View\ComponentSlot)
        {!! $icon !!}
      @else
        <x-ui.icon :name="$icon" class="h-4 w-4 mt-0.5" />
      @endif
    @endif
    <div class="space-y-1">
      {{ $slot }}
    </div>
  </div>
  </div>
