
@php $role = auth()->user()->role ?? null; @endphp
@if($role === 'supply_officer')
<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold tracking-tight">Supply Officer Dashboard</h1>
    <p class="text-muted-foreground">Welcome! Use the sidebar to manage supplies and view your activity.</p>
  </div>
  <x-ui.card class="p-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">Supply SKUs</p>
        <p class="text-2xl font-semibold">{{ number_format($supplySkus ?? 0) }}</p>
      </div>
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">Low stock</p>
        <p class="text-2xl font-semibold">{{ number_format($lowStock ?? 0) }}</p>
      </div>
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">On-hand value</p>
        <p class="text-2xl font-semibold">₱{{ number_format($suppliesValue ?? 0, 2) }}</p>
      </div>
    </div>
  </x-ui.card>
  <x-ui.card class="p-4 mt-4">
    <div class="text-sm text-gray-600">For full analytics, go to <a href='{{ route('supplies.analytics') }}' class='text-primary hover:underline'>Supply Analytics</a>.</div>
  </x-ui.card>
</div>
@else
<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
      <p class="text-muted-foreground">Overview of assets, supplies, and activity</p>
    </div>
  </div>
  {{-- ...existing code... --}}
@endif
