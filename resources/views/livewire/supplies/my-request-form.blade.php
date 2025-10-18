<div>
    <form wire:submit.prevent="submit">
        <h2 class="font-bold mb-2">Request Supplies</h2>
        <div class="mb-4">
            <label class="block mb-1">Select Supplies:</label>
            <select wire:model="items" multiple class="w-full border rounded">
                @foreach($availableSupplies as $supply)
                    <option value="{{ $supply->id }}">{{ $supply->description }}</option>
                @endforeach
            </select>
        </div>
        @foreach($items as $itemId)
            <div class="mb-2">
                <label>Quantity for {{ $supplyDescriptions[$itemId] ?? 'Unknown' }}:</label>
                <input type="number" min="1" wire:model="quantities.{{ $itemId }}" class="border rounded w-20" />
            </div>
        @endforeach

        <div class="mb-4">
            <label class="block mb-1">Remarks (optional):</label>
            <textarea wire:model="remarks" class="w-full border rounded" rows="2"></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Submit Request</button>
        @if (session()->has('success'))
            <div class="mt-2 text-green-600">{{ session('success') }}</div>
        @endif
        @error('items') <div class="text-red-600">{{ $message }}</div> @enderror
    </form>
</div>
