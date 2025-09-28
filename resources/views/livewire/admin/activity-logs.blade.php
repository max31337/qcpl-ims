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
                                    @if($log->old_values || $log->new_values)
                                        <button class="text-sm text-blue-600 hover:text-blue-800" 
                                                x-data 
                                                @click="$dispatch('show-activity-details', { log: @js($log) })">
                                            View Details
                                        </button>
                                    @else
                                        <span class="text-sm text-muted-foreground">—</span>
                                    @endif
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
</div>
