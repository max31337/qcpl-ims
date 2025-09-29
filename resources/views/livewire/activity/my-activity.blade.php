<div>
    <x-ui-card title="My Activity">
        <div class="flex items-center gap-3 mb-4">
            <x-ui-input wire:model.debounce.300ms="search" placeholder="Search my activity..." />
            <x-ui-select wire:model="actionFilter">
                <option value="">All actions</option>
                <option value="created">Created</option>
                <option value="updated">Updated</option>
                <option value="deleted">Deleted</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
            </x-ui-select>
        </div>

        <div class="overflow-hidden bg-white rounded shadow">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2">When</th>
                        <th class="p-2">Action</th>
                        <th class="p-2">Description</th>
                        <th class="p-2">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr class="border-t">
                            <td class="p-2">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                            <td class="p-2">{{ ucfirst($log->action) }}</td>
                            <td class="p-2">{{ $log->description }}</td>
                            <td class="p-2"><x-ui-button wire:click="showDetails({{ $log->id }})" size="sm">View</x-ui-button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </x-ui-card>

    @if($showModal && $selectedLog)
        <x-ui-modal wire:model="showModal">
            <x-slot name="title">Activity Details</x-slot>
            <div>
                <p class="text-sm text-gray-600">{{ $selectedLog->description }}</p>
                <pre class="mt-3 text-xs bg-gray-100 p-2 rounded">{{ json_encode($selectedLog->getAllChanges(), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </x-ui-modal>
    @endif
</div>
