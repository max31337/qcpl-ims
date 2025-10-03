<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('supplies.index') }}" wire:navigate class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-accent">
                <x-ui.icon name="arrow-left" class="mr-2 h-4 w-4" /> Back
            </a>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Adjust Stock</h1>
                <p class="text-muted-foreground">{{ $supply->supply_number }} • {{ $supply->description }}</p>
            </div>
        </div>
    </div>

    <form wire:submit="adjust" class="rounded-lg border bg-white shadow-sm">
        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-ui.label>Current Stock</x-ui.label>
                <x-ui.input value="{{ $supply->current_stock }}" readonly class="bg-muted" />
            </div>
            <div>
                <x-ui.label>Min Stock</x-ui.label>
                <x-ui.input value="{{ $supply->min_stock }}" readonly class="bg-muted" />
            </div>
            <div class="md:col-span-2">
                <x-ui.label>Quantity (use negative to deduct)</x-ui.label>
                <x-ui.input type="number" wire:model="quantity" />
                @error('quantity') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <x-ui.label>Remarks</x-ui.label>
                <x-ui.textarea rows="3" wire:model="remarks" />
                @error('remarks') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="px-6 py-4 border-t flex items-center justify-end gap-2">
            <a href="{{ route('supplies.index') }}" wire:navigate class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-accent">Cancel</a>
            <button type="submit" class="inline-flex items-center rounded-md bg-primary text-primary-foreground px-3 py-2 text-sm hover:bg-primary/90">Apply Adjustment</button>
        </div>
    </form>
</div>
