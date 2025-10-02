<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">My Activity</h1>
            <p class="text-muted-foreground">View your personal activity history and actions</p>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Search</label>
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search activities..." class="mt-1.5" />
            </div>
            
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Action</label>
                <select wire:model.live="actionFilter"
                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">All Actions</option>
                    @foreach($availableActions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Model</label>
                <select wire:model.live="modelFilter"
                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">All Models</option>
                    @foreach($availableModels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">From Date</label>
                <x-ui.input wire:model.live="dateFromFilter" type="date" class="mt-1.5" />
            </div>

            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">To Date</label>
                <x-ui.input wire:model.live="dateToFilter" type="date" class="mt-1.5" />
            </div>
        </div>

        <div class="flex justify-between items-center mt-4 pt-4 border-t border-border">
            <div class="flex items-center gap-2">
                <span class="text-sm text-muted-foreground">Show:</span>
                <select wire:model.live="perPage"
                        class="flex h-9 w-20 rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-muted-foreground">entries</span>
            </div>
            
            <x-ui.button wire:click="clearFilters" variant="outline" size="sm">
                <x-ui.icon name="x" class="mr-2 h-4 w-4" />
                Clear Filters
            </x-ui.button>
        </div>
    </x-ui.card>

    <!-- Activity Table -->
    <x-ui.card>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left py-3 px-4 font-medium text-muted-foreground">Timestamp</th>
                        <th class="text-left py-3 px-4 font-medium text-muted-foreground">Action</th>
                        <th class="text-left py-3 px-4 font-medium text-muted-foreground">Model</th>
                        <th class="text-left py-3 px-4 font-medium text-muted-foreground">Description</th>
                        <th class="text-left py-3 px-4 font-medium text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-b border-border hover:bg-muted/50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="text-sm font-medium">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-muted-foreground">{{ $log->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                    @if($log->action === 'created') bg-green-100 text-green-700
                                    @elseif($log->action === 'updated') bg-blue-100 text-blue-700
                                    @elseif($log->action === 'deleted') bg-red-100 text-red-700
                                    @elseif($log->action === 'login') bg-emerald-100 text-emerald-700
                                    @elseif($log->action === 'logout') bg-gray-100 text-gray-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm font-medium">{{ $log->model ? class_basename($log->model) : '-' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-sm">{{ $log->friendly_description ?? $log->description }}</div>
                                @if($log->model_id)
                                    <div class="text-xs text-muted-foreground">ID: {{ $log->model_id }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <x-ui.button wire:click="showDetails({{ $log->id }})" variant="outline" size="sm">
                                    <x-ui.icon name="eye" class="mr-1 h-3 w-3" />
                                    View
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-muted-foreground">
                                <x-ui.icon name="inbox" class="mx-auto h-12 w-12 mb-4 text-muted-foreground/50" />
                                <div class="text-sm">No activities found</div>
                                <div class="text-xs">Try adjusting your filters or date range</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-border">
                {{ $logs->links('pagination::custom-light') }}
            </div>
        @endif
    </x-ui.card>

    <!-- Activity Details Modal -->
    @if($showModal && $selectedLog)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Activity Details</h3>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <x-ui.icon name="x" class="h-6 w-6" />
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-500">Action:</span>
                                    <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $selectedLog->action)) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-500">Model:</span>
                                    <span class="ml-2">{{ $selectedLog->model ? class_basename($selectedLog->model) : '-' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-500">User:</span>
                                    <span class="ml-2">{{ $selectedLog->user->name ?? 'System' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-500">Timestamp:</span>
                                    <span class="ml-2">{{ $selectedLog->created_at->format('M d, Y g:i A') }}</span>
                                </div>
                            </div>
                            
                            <div>
                                <span class="font-medium text-gray-500">Description:</span>
                                <div class="mt-1 text-sm text-gray-900">{{ $selectedLog->friendly_description ?? $selectedLog->description }}</div>
                            </div>

                            @if($selectedLog->old_values && $selectedLog->new_values)
                                @php
                                    $changes = $selectedLog->getFormattedChanges();
                                @endphp
                                @if(count($changes) > 0)
                                    <div>
                                        <span class="font-medium text-gray-500">Changes:</span>
                                        <div class="mt-2 overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Field</th>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Old Value</th>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">New Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($changes as $change)
                                                        <tr>
                                                            <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ $change['field_name'] }}</td>
                                                            <td class="px-3 py-2 text-sm text-gray-500">{{ $change['old_value'] }}</td>
                                                            <td class="px-3 py-2 text-sm text-gray-500">{{ $change['new_value'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <x-ui.button wire:click="closeModal" variant="outline">Close</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</div>
