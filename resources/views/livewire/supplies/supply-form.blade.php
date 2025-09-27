<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('supplies.index') }}" wire:navigate class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-accent">
                <x-ui.icon name="arrow-left" class="mr-2 h-4 w-4" /> Back
            </a>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ $supplyId ? 'Edit Supply' : 'Create Supply' }}</h1>
                <p class="text-muted-foreground">Fill in supply details and stock thresholds</p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="rounded-lg border bg-white shadow-sm">
        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-ui.label>Supply Number</x-ui.label>
                <x-ui.input wire:model="supply_number" readonly class="bg-muted" />
                @error('supply_number') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <x-ui.label>Status</x-ui.label>
                <select wire:model="status" class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                @error('status') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <x-ui.label>Description</x-ui.label>
                <x-ui.textarea wire:model="description" rows="3" />
                @error('description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-ui.label>Category</x-ui.label>
                <select wire:model="category_id" class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <x-ui.label>Branch</x-ui.label>
                <select wire:model="branch_id" class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
                @error('branch_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-ui.label>Current Stock</x-ui.label>
                <x-ui.input type="number" min="0" wire:model="current_stock" />
                @error('current_stock') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <x-ui.label>Minimum Stock</x-ui.label>
                <x-ui.input type="number" min="0" wire:model="min_stock" />
                @error('min_stock') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <x-ui.label>Unit Cost (₱)</x-ui.label>
                <x-ui.input type="number" step="0.01" min="0" wire:model="unit_cost" />
                @error('unit_cost') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="px-6 py-4 border-t flex items-center justify-end gap-2">
            <a href="{{ route('supplies.index') }}" wire:navigate class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-accent">Cancel</a>
            <button type="submit" class="inline-flex items-center rounded-md bg-primary text-primary-foreground px-3 py-2 text-sm hover:bg-primary/90">Save</button>
        </div>
    </form>
</div>
