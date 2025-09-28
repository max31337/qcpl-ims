<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Activity Logs</h1>
            <p class="text-muted-foreground">Monitor all user activities and system events</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button wire:click="exportLogs" variant="outline">
                <x-ui.icon name="download" class="mr-2 h-4 w-4" />
                Export Logs
            </x-ui.button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            <div class="flex">
                <x-ui.icon name="check" class="h-4 w-4 text-green-400" />
                <div class="ml-3">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-ui.card class="p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-full">
                    <x-ui.icon name="activity" class="h-5 w-5 text-blue-600" />
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Activities</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['total_activities']) }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 rounded-full">
                    <x-ui.icon name="users" class="h-5 w-5 text-green-600" />
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Active Users</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['unique_users']) }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 rounded-full">
                    <x-ui.icon name="log-in" class="h-5 w-5 text-purple-600" />
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Recent Logins</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['recent_logins']) }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-orange-100 rounded-full">
                    <x-ui.icon name="pencil" class="h-5 w-5 text-orange-600" />
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Data Changes</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['data_changes']) }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- Filters -->
    <x-ui.card class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Search</label>
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search activities..." class="mt-1.5" />
            </div>
            
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">User</label>
                <select wire:model.live="userFilter"
                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
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

            @if($search || $userFilter || $actionFilter || $modelFilter || $dateFromFilter !== now()->subDays(30)->format('Y-m-d') || $dateToFilter !== now()->format('Y-m-d'))
                <x-ui.button wire:click="clearFilters" variant="outline">
                    <x-ui.icon name="refresh-ccw" class="mr-2 h-4 w-4" />
                    Clear Filters
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>

    <!-- Activity Logs Table -->
    <x-ui.card class="overflow-hidden">
        @if($logs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Model</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-background divide-y divide-border">
                        @foreach($logs as $log)
                            <tr class="hover:bg-muted/25">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <x-ui.icon name="{{ $log->action_icon }}" class="h-4 w-4 {{ $log->action_color }}" />
                                        <span class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-primary/10 text-primary rounded-full flex items-center justify-center text-xs font-semibold">
                                            {{ $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SY' }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium">{{ $log->user->name ?? 'System' }}</div>
                                            <div class="text-xs text-muted-foreground">{{ $log->user->role ?? 'system' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-foreground max-w-md">{{ $log->description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->model)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $log->model }} #{{ $log->model_id }}
                                        </span>
                                    @else
                                        <span class="text-sm text-muted-foreground">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-foreground">{{ $log->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $log->created_at->format('g:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="text-sm text-blue-600 hover:text-blue-800" 
                                            wire:click="showDetails({{ $log->id }})">
                                        <div class="flex items-center gap-1">
                                            @if(in_array($log->action, ['login', 'logout']) && $log->ip_address)
                                                <x-ui.icon name="shield-check" size="sm" />
                                                Security Details
                                            @else
                                                <x-ui.icon name="info" size="sm" />
                                                View Details
                                            @endif
                                        </div>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-border">
                {{ $logs->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <x-ui.icon name="activity" class="mx-auto h-12 w-12 text-muted-foreground" />
                <h3 class="mt-4 text-lg font-semibold">No activity logs found</h3>
                <p class="mt-2 text-muted-foreground">Try adjusting your filters or date range.</p>
            </div>
        @endif
    </x-ui.card>

    <!-- Activity Details Modal -->
    @if($showModal && $selectedLog)
        <div class="fixed inset-0 z-50 overflow-y-auto" 
             x-data 
             @keydown.escape.window="$wire.closeModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                     wire:click="closeModal"></div>

                <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="info" class="h-5 w-5 text-blue-600" />
                                <h3 class="text-lg font-semibold text-gray-900">Activity Details</h3>
                            </div>
                            <button wire:click="closeModal"
                                    class="text-gray-400 hover:text-gray-600">
                                <x-ui.icon name="x" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 max-h-96 overflow-y-auto">
                        <div class="space-y-6">
                            <!-- Basic Info (Always shown) -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Activity Information</h4>
                                    <div class="space-y-1 text-sm">
                                        <div><span class="font-medium">Action:</span> <span class="capitalize">{{ $selectedLog->action ?? 'N/A' }}</span></div>
                                        <div><span class="font-medium">Time:</span> {{ $selectedLog->created_at ? $selectedLog->created_at->format('M d, Y g:i A') : 'N/A' }}</div>
                                        <div><span class="font-medium">User:</span> {{ $selectedLog->user->name ?? 'N/A' }}</div>
                                        @if($selectedLog->model)
                                            <div><span class="font-medium">Model:</span> {{ $selectedLog->model }} #{{ $selectedLog->model_id }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Description</h4>
                                    <div class="text-sm p-3 bg-gray-50 rounded-lg">
                                        {{ $selectedLog->description ?? 'No description' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Asset Information (For Asset activities) -->
                            @if($selectedLog->model === 'Asset' && ($selectedLog->old_values || $selectedLog->new_values))
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                        <x-ui.icon name="package" class="h-3 w-3 text-blue-600" />
                                        Asset Information
                                    </h4>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            @if($selectedLog->old_values['property_number'] ?? null)
                                                <div>
                                                    <span class="font-medium text-blue-800">Property Number:</span>
                                                    <div class="text-blue-700 font-mono">{{ $selectedLog->old_values['property_number'] }}</div>
                                                </div>
                                            @endif
                                            @if($selectedLog->old_values['description'] ?? null)
                                                <div>
                                                    <span class="font-medium text-blue-800">Asset Description:</span>
                                                    <div class="text-blue-700">{{ $selectedLog->old_values['description'] }}</div>
                                                </div>
                                            @endif
                                            @if(($selectedLog->old_values['unit_cost'] ?? null) && ($selectedLog->old_values['quantity'] ?? null))
                                                <div>
                                                    <span class="font-medium text-blue-800">Value:</span>
                                                    <div class="text-blue-700">
                                                        ₱{{ number_format($selectedLog->old_values['unit_cost'], 2) }} 
                                                        × {{ $selectedLog->old_values['quantity'] }} = 
                                                        ₱{{ number_format($selectedLog->old_values['unit_cost'] * $selectedLog->old_values['quantity'], 2) }}
                                                    </div>
                                                </div>
                                            @endif
                                            @if($selectedLog->old_values['date_acquired'] ?? null)
                                                <div>
                                                    <span class="font-medium text-blue-800">Date Acquired:</span>
                                                    <div class="text-blue-700">{{ \Carbon\Carbon::parse($selectedLog->old_values['date_acquired'])->format('M d, Y') }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Asset Transfer Information (For transfer activities) -->
                            @if($selectedLog->model === 'AssetTransferHistory' && ($selectedLog->old_values || $selectedLog->new_values))
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                        <x-ui.icon name="transfer" class="h-3 w-3 text-purple-600" />
                                        Transfer Information
                                    </h4>
                                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                        <div class="space-y-4">
                                            <!-- Asset Being Transferred -->
                                            @if(($selectedLog->new_values['asset_id'] ?? null) || ($selectedLog->old_values['asset_id'] ?? null))
                                                @php
                                                    $assetId = $selectedLog->new_values['asset_id'] ?? $selectedLog->old_values['asset_id'];
                                                    $asset = \App\Models\Asset::find($assetId);
                                                @endphp
                                                @if($asset)
                                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                                        <h5 class="text-sm font-semibold text-blue-900 mb-2">Asset Being Transferred</h5>
                                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                                            <div>
                                                                <span class="font-medium text-blue-800">Property Number:</span>
                                                                <div class="text-blue-700 font-mono">{{ $asset->property_number }}</div>
                                                            </div>
                                                            <div>
                                                                <span class="font-medium text-blue-800">Description:</span>
                                                                <div class="text-blue-700">{{ $asset->description }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif

                                            <!-- Transfer Details -->
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                @php
                                                    $originBranch = $selectedLog->new_values['origin_branch_id'] ? \App\Models\Branch::find($selectedLog->new_values['origin_branch_id']) : null;
                                                    $currentBranch = $selectedLog->new_values['current_branch_id'] ? \App\Models\Branch::find($selectedLog->new_values['current_branch_id']) : null;
                                                    $originDivision = $selectedLog->new_values['origin_division_id'] ? \App\Models\Division::find($selectedLog->new_values['origin_division_id']) : null;
                                                    $currentDivision = $selectedLog->new_values['current_division_id'] ? \App\Models\Division::find($selectedLog->new_values['current_division_id']) : null;
                                                    $originSection = $selectedLog->new_values['origin_section_id'] ? \App\Models\Section::find($selectedLog->new_values['origin_section_id']) : null;
                                                    $currentSection = $selectedLog->new_values['current_section_id'] ? \App\Models\Section::find($selectedLog->new_values['current_section_id']) : null;
                                                    $transferredBy = $selectedLog->new_values['transferred_by'] ? \App\Models\User::find($selectedLog->new_values['transferred_by']) : null;
                                                @endphp

                                                @if($originBranch)
                                                    <div>
                                                        <span class="font-medium text-purple-800">From Branch:</span>
                                                        <div class="text-purple-700">{{ $originBranch->name }}</div>
                                                    </div>
                                                @endif
                                                @if($currentBranch)
                                                    <div>
                                                        <span class="font-medium text-purple-800">To Branch:</span>
                                                        <div class="text-purple-700">{{ $currentBranch->name }}</div>
                                                    </div>
                                                @endif
                                                @if($originDivision)
                                                    <div>
                                                        <span class="font-medium text-purple-800">From Division:</span>
                                                        <div class="text-purple-700">{{ $originDivision->name }}</div>
                                                    </div>
                                                @endif
                                                @if($currentDivision)
                                                    <div>
                                                        <span class="font-medium text-purple-800">To Division:</span>
                                                        <div class="text-purple-700">{{ $currentDivision->name }}</div>
                                                    </div>
                                                @endif
                                                @if($originSection)
                                                    <div>
                                                        <span class="font-medium text-purple-800">From Section:</span>
                                                        <div class="text-purple-700">{{ $originSection->name }}</div>
                                                    </div>
                                                @endif
                                                @if($currentSection)
                                                    <div>
                                                        <span class="font-medium text-purple-800">To Section:</span>
                                                        <div class="text-purple-700">{{ $currentSection->name }}</div>
                                                    </div>
                                                @endif
                                                @if($selectedLog->new_values['transfer_date'] ?? null)
                                                    <div>
                                                        <span class="font-medium text-purple-800">Transfer Date:</span>
                                                        <div class="text-purple-700">{{ \Carbon\Carbon::parse($selectedLog->new_values['transfer_date'])->format('M d, Y g:i A') }}</div>
                                                    </div>
                                                @endif
                                                @if($transferredBy)
                                                    <div>
                                                        <span class="font-medium text-purple-800">Transferred By:</span>
                                                        <div class="text-purple-700">{{ $transferredBy->name }}</div>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Transfer Remarks -->
                                            @if($selectedLog->new_values['remarks'] ?? null)
                                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                                    <span class="font-medium text-gray-800">Transfer Remarks:</span>
                                                    <div class="text-gray-700 mt-1">{{ $selectedLog->new_values['remarks'] }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Security Info (Login/Logout only) -->
                            @if($selectedLog->ip_address)
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                        <x-ui.icon name="shield-check" size="sm" />
                                        Security Information
                                    </h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <h5 class="text-xs font-semibold text-gray-700 mb-2">Network</h5>
                                            <div class="space-y-1 text-sm">
                                                <div><span class="font-medium">IP Address:</span> <span class="font-mono">{{ $selectedLog->ip_address }}</span></div>
                                                <div><span class="font-medium">Session:</span> <span class="font-mono text-xs">{{ substr($selectedLog->session_id ?? '', 0, 16) }}{{ $selectedLog->session_id ? '...' : '' }}</span></div>
                                            </div>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-semibold text-gray-700 mb-2">Browser & Device</h5>
                                            <div class="space-y-1 text-sm">
                                                <div><span class="font-medium">Browser:</span> {{ $selectedLog->browser }} {{ $selectedLog->browser_version }}</div>
                                                <div><span class="font-medium">Platform:</span> {{ $selectedLog->platform }}</div>
                                                <div><span class="font-medium">Device:</span> <span class="capitalize">{{ $selectedLog->device }}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Device Type Badges -->
                                    @if($selectedLog->request_data)
                                        <div class="mt-3">
                                            <h5 class="text-xs font-semibold text-gray-700 mb-2">Device Type</h5>
                                            <div class="flex flex-wrap gap-2">
                                                @if($selectedLog->request_data['is_mobile'] ?? false)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <x-ui.icon name="smartphone" size="xs" class="mr-1" /> Mobile
                                                    </span>
                                                @endif
                                                @if($selectedLog->request_data['is_tablet'] ?? false)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <x-ui.icon name="tablet" size="xs" class="mr-1" /> Tablet
                                                    </span>
                                                @endif
                                                @if($selectedLog->request_data['is_desktop'] ?? false)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        <x-ui.icon name="monitor" size="xs" class="mr-1" /> Desktop
                                                    </span>
                                                @endif
                                                @if($selectedLog->request_data['is_robot'] ?? false)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <x-ui.icon name="bot" size="xs" class="mr-1" /> Bot
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Changes Summary (For update activities) -->
                            @if($selectedLog->old_values && count($selectedLog->old_values) > 0)
                                <div class="border-t pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            <x-ui.icon name="pencil" size="sm" />
                                            Changes Made
                                        </h4>
                                        @if(count($selectedLog->getAllChanges()) > 0)
                                            <button wire:click="toggleRawDetails({{ $selectedLog->id }})" 
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md transition-colors">
                                                <x-ui.icon name="code" size="xs" />
                                                @if($showRawDetails === $selectedLog->id) 
                                                    Hide Technical Details
                                                @else 
                                                    Show Technical Details
                                                @endif
                                            </button>
                                        @endif
                                    </div>

                                    <!-- User-Friendly Summary -->
                                    @php $summary = $selectedLog->getChangesSummary(); @endphp
                                    @if($summary)
                                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 mb-4">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <x-ui.icon name="info" size="sm" class="text-blue-600" />
                                                </div>
                                                <div class="flex-1">
                                                    <h5 class="text-sm font-semibold text-blue-900 mb-1">What Changed</h5>
                                                    <p class="text-sm text-blue-800 font-medium">{{ $summary }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Important Changes Only -->
                                    @php $changes = $selectedLog->getFormattedChanges(); @endphp
                                    @if(count($changes) > 0 && $showRawDetails !== $selectedLog->id)
                                        <div class="space-y-3">
                                            @foreach($changes as $change)
                                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <h5 class="text-sm font-semibold text-gray-900">{{ $change['field_name'] }}</h5>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                                            <span class="text-xs font-semibold text-red-700 flex items-center gap-1">
                                                                <x-ui.icon name="minus-circle" size="xs" />
                                                                Previous
                                                            </span>
                                                            <div class="text-sm text-red-700 mt-1 font-medium">
                                                                {{ $change['old_value'] }}
                                                            </div>
                                                        </div>
                                                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                                            <span class="text-xs font-semibold text-green-700 flex items-center gap-1">
                                                                <x-ui.icon name="plus-circle" size="xs" />
                                                                New
                                                            </span>
                                                            <div class="text-sm text-green-700 mt-1 font-medium">
                                                                {{ $change['new_value'] }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Raw Details (All Changes) -->
                                    @if($showRawDetails === $selectedLog->id)
                                        <div class="space-y-2">
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                                <div class="flex items-center gap-2 text-yellow-800">
                                                    <x-ui.icon name="alert-triangle" size="sm" />
                                                    <span class="text-xs font-semibold">Raw Technical Details</span>
                                                </div>
                                                <p class="text-xs text-yellow-700 mt-1">This shows all database field changes, including system-generated data.</p>
                                            </div>

                                            @foreach($selectedLog->getAllChanges() as $change)
                                                <div class="bg-gray-50 rounded border border-gray-200 text-xs">
                                                    <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 bg-gray-100">
                                                        <span class="font-semibold text-gray-800">{{ $change['field_name'] }}</span>
                                                        <div class="flex items-center gap-2">
                                                            @if($change['is_system_field'])
                                                                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs">System Field</span>
                                                            @endif
                                                            <span class="font-mono text-gray-500">{{ $change['field'] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-2 divide-x divide-gray-200">
                                                        <div class="p-3">
                                                            <div class="text-red-600 font-semibold mb-1">Previous Value</div>
                                                            <div class="text-gray-800 break-all">{{ $change['old_value'] }}</div>
                                                        </div>
                                                        <div class="p-3">
                                                            <div class="text-green-600 font-semibold mb-1">New Value</div>
                                                            <div class="text-gray-800 break-all">{{ $change['new_value'] }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(count($changes) === 0 && !$summary)
                                        <div class="text-center py-6 text-gray-500">
                                            <x-ui.icon name="info" size="lg" class="mx-auto mb-2 text-gray-400" />
                                            <p class="text-sm font-medium">No detailed changes to display</p>
                                            <p class="text-xs text-gray-400 mt-1">This activity may not have tracked field changes</p>
                                        </div>
                                    @elseif(count($changes) === 0 && $summary && $showRawDetails !== $selectedLog->id)
                                        <div class="text-center py-4 text-green-600 bg-green-50 rounded-lg border border-green-200">
                                            <x-ui.icon name="check-circle" class="h-4 w-4 mx-auto mb-2" />
                                            <p class="text-sm font-medium">Summary view active</p>
                                            <p class="text-xs text-green-700 mt-1">The change summary above shows the key information. Click "Show Technical Details" for raw data.</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Request Details (For security logs) -->
                            @if($selectedLog->request_data)
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                        <x-ui.icon name="info" size="sm" />
                                        Request Information
                                    </h4>
                                    <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-sm">
                                        <div><span class="font-medium">URL:</span> <span class="font-mono text-xs break-all">{{ $selectedLog->request_data['url'] ?? 'N/A' }}</span></div>
                                        <div><span class="font-medium">Method:</span> <span class="font-mono bg-gray-200 px-1 rounded">{{ $selectedLog->request_data['method'] ?? 'N/A' }}</span></div>
                                        @if($selectedLog->request_data['referer'] ?? false)
                                            <div><span class="font-medium">Referer:</span> <span class="font-mono text-xs break-all">{{ $selectedLog->request_data['referer'] }}</span></div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- User Agent (For security logs) -->
                            @if($selectedLog->user_agent)
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3">User Agent</h4>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <code class="text-xs break-all">{{ $selectedLog->user_agent }}</code>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-end">
                            <button wire:click="closeModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
