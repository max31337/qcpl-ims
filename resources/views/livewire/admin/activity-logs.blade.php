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
                                            @click="$dispatch('show-activity-details', { log: @js($log) })">
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
    <div x-data="{ 
        showModal: false, 
        activityLog: null 
    }"
         @show-activity-details.window="showModal = true; activityLog = $event.detail.log"
         @keydown.escape.window="showModal = false">
        
        <div x-show="showModal" 
             x-transition.opacity
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                     @click="showModal = false"></div>

                <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-ui.icon x-bind:name="activityLog?.action === 'login' || activityLog?.action === 'logout' ? 'shield-check' : 'info'" class="h-5 w-5 text-blue-600" />
                                <h3 class="text-lg font-semibold text-gray-900" x-text="activityLog?.action === 'login' || activityLog?.action === 'logout' ? 'Security & Activity Details' : 'Activity Details'"></h3>
                            </div>
                            <button @click="showModal = false"
                                    class="text-gray-400 hover:text-gray-600">
                                <x-ui.icon name="x" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 max-h-96 overflow-y-auto" x-show="activityLog">
                        <div class="space-y-6">
                            <!-- Basic Info (Always shown) -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Activity Information</h4>
                                    <div class="space-y-1 text-sm">
                                        <div><span class="font-medium">Action:</span> <span x-text="activityLog?.action" class="capitalize"></span></div>
                                        <div><span class="font-medium">Time:</span> <span x-text="activityLog?.created_at"></span></div>
                                        <div><span class="font-medium">User:</span> <span x-text="activityLog?.user?.name"></span></div>
                                        <div x-show="activityLog?.model"><span class="font-medium">Model:</span> <span x-text="activityLog?.model"></span> #<span x-text="activityLog?.model_id"></span></div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Description</h4>
                                    <div class="text-sm p-3 bg-gray-50 rounded-lg">
                                        <span x-text="activityLog?.description"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Info (Login/Logout only) -->
                            <div x-show="activityLog?.ip_address" class="border-t pt-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <x-ui.icon name="shield-check" size="sm" />
                                    Security Information
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <h5 class="text-xs font-semibold text-gray-700 mb-2">Network</h5>
                                        <div class="space-y-1 text-sm">
                                            <div><span class="font-medium">IP Address:</span> <span x-text="activityLog?.ip_address" class="font-mono"></span></div>
                                            <div><span class="font-medium">Session:</span> <span x-text="activityLog?.session_id?.substring(0, 16) + '...'" class="font-mono text-xs"></span></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-semibold text-gray-700 mb-2">Browser & Device</h5>
                                        <div class="space-y-1 text-sm">
                                            <div><span class="font-medium">Browser:</span> <span x-text="activityLog?.browser"></span> <span x-text="activityLog?.browser_version"></span></div>
                                            <div><span class="font-medium">Platform:</span> <span x-text="activityLog?.platform"></span></div>
                                            <div><span class="font-medium">Device:</span> <span x-text="activityLog?.device" class="capitalize"></span></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Device Type Badges -->
                                <div class="mt-3">
                                    <h5 class="text-xs font-semibold text-gray-700 mb-2">Device Type</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <span x-show="activityLog?.request_data?.is_mobile" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <x-ui.icon name="smartphone" size="xs" class="mr-1" /> Mobile
                                        </span>
                                        <span x-show="activityLog?.request_data?.is_tablet" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <x-ui.icon name="tablet" size="xs" class="mr-1" /> Tablet
                                        </span>
                                        <span x-show="activityLog?.request_data?.is_desktop" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <x-ui.icon name="monitor" size="xs" class="mr-1" /> Desktop
                                        </span>
                                        <span x-show="activityLog?.request_data?.is_robot" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <x-ui.icon name="bot" size="xs" class="mr-1" /> Bot
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Changes (For update activities) -->
                            <div x-show="activityLog?.old_values && Object.keys(activityLog?.old_values || {}).length > 0" class="border-t pt-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <x-ui.icon name="pencil" size="sm" />
                                    Data Changes
                                </h4>
                                <div class="space-y-3">
                                    <template x-for="[field, value] in Object.entries(activityLog?.old_values || {})" :key="field">
                                        <div class="grid grid-cols-3 gap-4 p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <span class="text-xs font-semibold text-gray-700">Field</span>
                                                <div class="text-sm font-medium" x-text="field"></div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-semibold text-red-700">Old Value</span>
                                                <div class="text-sm text-red-600" x-text="value || '(empty)'"></div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-semibold text-green-700">New Value</span>
                                                <div class="text-sm text-green-600" x-text="activityLog?.new_values?.[field] || '(empty)'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Request Details (For security logs) -->
                            <div x-show="activityLog?.request_data" class="border-t pt-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <x-ui.icon name="info" size="sm" />
                                    Request Information
                                </h4>
                                <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-sm">
                                    <div><span class="font-medium">URL:</span> <span x-text="activityLog?.request_data?.url" class="font-mono text-xs break-all"></span></div>
                                    <div><span class="font-medium">Method:</span> <span x-text="activityLog?.request_data?.method" class="font-mono bg-gray-200 px-1 rounded"></span></div>
                                    <div x-show="activityLog?.request_data?.referer"><span class="font-medium">Referer:</span> <span x-text="activityLog?.request_data?.referer" class="font-mono text-xs break-all"></span></div>
                                </div>
                            </div>

                            <!-- User Agent (For security logs) -->
                            <div x-show="activityLog?.user_agent" class="border-t pt-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">User Agent</h4>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <code x-text="activityLog?.user_agent" class="text-xs break-all"></code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-end">
                            <button @click="showModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
