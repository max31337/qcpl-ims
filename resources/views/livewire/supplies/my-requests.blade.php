<!-- Livewire StaffSupplyRequests-->

<div x-data="{ showModal: false }">
    <h2 class="font-bold mb-4">My Supply Requests</h2>
    <button @click="showModal = true" class="mb-4 bg-blue-600 text-white px-4 py-2 rounded">Submit New Request</button>
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
                <th>Items</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                <tr>
                    <td>{{ $req->id }}</td>
                    <td>
                        @foreach(json_decode($req->items, true) as $item)
                            <div>
                                {{ $supplyNames[$item['supply_id']] ?? 'Unknown' }} (ID: {{ $item['supply_id'] }}), Qty: {{ $item['quantity'] }}
                            </div>
                        @endforeach
                    </td>
                    <td>{{ ucfirst(str_replace('_', ' ', $req->status)) }}</td>
                    <td>{{ $req->remarks ?? '-' }}</td>
                    <td>{{ $req->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No requests found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Modal for submitting request -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow w-full max-w-lg">
            <h3 class="font-bold mb-2">Submit New Supply Request</h3>
            <button @click="showModal = false" class="absolute top-2 right-2 text-gray-500">&times;</button>
            @livewire('supplies.my-request-form')
        </div>
    </div>
</div>
