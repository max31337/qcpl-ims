@props(['categories' => [], 'branches' => [], 'divisions' => [], 'sections' => [], 'showBranch' => true, 'bindPrefix' => ''])
@php
  $p = $bindPrefix ? $bindPrefix.'.' : '';
@endphp
<div class="grid grid-cols-1 md:grid-cols-8 gap-4">
  <div>
    <label class="text-sm font-medium">From</label>
  <input type="date" wire:model="{{ $p }}pendingDateFrom" class="mt-1.5 h-10 w-full rounded-md border px-3 text-sm" />
  </div>
  <div>
    <label class="text-sm font-medium">To</label>
  <input type="date" wire:model="{{ $p }}pendingDateTo" class="mt-1.5 h-10 w-full rounded-md border px-3 text-sm" />
  </div>
  <div>
    <label class="text-sm font-medium">Category</label>
  <select wire:model="{{ $p }}pendingCategoryFilter" class="mt-1.5 h-10 w-full rounded-md border px-3 text-sm">
      <option value="">All</option>
      @foreach($categories as $c)
        <option value="{{ $c->id }}">{{ $c->name }}</option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="text-sm font-medium">Status</label>
  <select wire:model="{{ $p }}pendingStatusFilter" class="mt-1.5 h-10 w-full rounded-md border px-3 text-sm">
      <option value="">All</option>
      <option value="active">Active</option>
      <option value="condemn">Condemn</option>
      <option value="disposed">Disposed</option>
    </select>
  </div>
  @if($showBranch && count($branches) > 1)
  <div>
    <label class="text-sm font-medium">Branch</label>
  <select wire:model.live="{{ $p }}pendingBranchFilter" class="mt-1.5 h-10 w-full rounded-md border px-3 text-sm">
      <option value="">All</option>
      @foreach($branches as $b)
        <option value="{{ $b->id }}">{{ $b->name }}</option>
      @endforeach
    </select>
  </div>
  @endif
  <div>
    <label class="text-sm font-medium">Division</label>
  <select wire:model.live="{{ $p }}pendingDivisionFilter" class="mt-1.5 h-10 w-full rounded-md border px-3 text-sm" @disabled(! $attributes->get('branches') )>
      <option value="">All</option>
      @foreach($divisions as $d)
        <option value="{{ $d->id }}">{{ $d->name }}</option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="text-sm font-medium">Section</label>
  <select wire:model.live="{{ $p }}pendingSectionFilter" class="mt-1.5 h-10 w-full rounded-md border px-3 text-sm">
      <option value="">All</option>
      @foreach($sections as $s)
        <option value="{{ $s->id }}">{{ $s->name }}</option>
      @endforeach
    </select>
  </div>
</div>
