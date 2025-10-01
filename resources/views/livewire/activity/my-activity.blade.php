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
                            <td class="p-2">{{ $log->friendly_description ?? $log->description }}</td>
                            <td class="p-2"><x-ui-button x-data x-on:click="$dispatch('open-modal', '{{ 'activity-'.$log->id }}')" size="sm">View</x-ui-button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </x-ui-card>

    @foreach($logs as $log)
        <x-ui-modal name="{{ 'activity-'.$log->id }}" :show="false" maxWidth="3xl">
            <x-slot name="title">Activity Details</x-slot>
            <div class="p-6">
                <p class="text-sm text-gray-700 mb-3">{{ $log->friendly_description ?? $log->description }}</p>
                @php $changes = $log->getAllChanges(); @endphp
                @if(!empty($changes))
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-3 py-2 text-left">Field</th>
                                    <th class="px-3 py-2 text-left">Old</th>
                                    <th class="px-3 py-2 text-left">New</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($changes as $c)
                                    <tr class="border-t">
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $c['field_name'] }}</td>
                                        <td class="px-3 py-2">{{ $c['old_value'] }}</td>
                                        <td class="px-3 py-2">{{ $c['new_value'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-sm text-gray-500">No field-level changes recorded.</div>
                @endif
                <div class="mt-4 text-xs text-gray-400">Logged at {{ $log->created_at->format('M d, Y g:i A') }}</div>
            </div>
        </x-ui-modal>
    @endforeach
</div>
