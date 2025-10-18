<div>
    <h2 class="font-bold mb-4">Pending Supply Requests</h2>
    @if (session()->has('success'))
        <div class="text-green-600">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="text-red-600">{{ session('error') }}</div>
    @endif
    <table class="w-full border">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Items</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                <tr>
                    <td>{{ $req->id }}</td>
                    <td>{{ $req->user_id }}</td>
                    <td>
                        @foreach(json_decode($req->items, true) as $item)
                            <div>Supply ID: {{ $item['supply_id'] }}, Qty: {{ $item['quantity'] }}</div>
                        @endforeach
                    </td>
                    <td>{{ $req->status }}</td>
                    <td>
                        <button @click="window.livewire.emit('openModal', 'approve', {{ $req->id }})" class="bg-green-500 text-white px-2 py-1 rounded">Approve</button>
                        <button @click="window.livewire.emit('openModal', 'reject', {{ $req->id }})" class="bg-red-500 text-white px-2 py-1 rounded">Reject</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No pending requests.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Modal -->
    <div x-data="{ show: false, action: '', reqId: null }"
         x-on:openModal.window="show = true; action = $event.detail.action; reqId = $event.detail.id"
         x-show="show"
         style="display: none;"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow w-96">
            <h3 class="font-bold mb-2" x-text="action === 'approve' ? 'Approve Request' : 'Reject Request'"></h3>
            <p class="mb-4">Are you sure you want to <span x-text="action"></span> this request?</p>
            <div class="flex justify-end gap-2">
                <button @click="show = false" class="px-3 py-1 bg-gray-300 rounded">Cancel</button>
                <button 
                    x-show="action === 'approve'"
                    wire:click="approve(reqId)"
                    @click="show = false"
                    class="px-3 py-1 bg-green-500 text-white rounded">
                    Confirm
                </button>
                <button 
                    x-show="action === 'reject'"
                    wire:click="reject(reqId)"
                    @click="show = false"
                    class="px-3 py-1 bg-red-500 text-white rounded">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>
