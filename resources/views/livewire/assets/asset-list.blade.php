<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Assets Management</h1>
            <p class="text-muted-foreground">Manage and track all library assets and properties</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('assets.transfer-histories') }}" class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-accent">
                <x-ui.icon name="history" class="mr-2 h-4 w-4" />
                Transfer Histories
            </a>
            <a href="{{ route('assets.reports') }}" class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-accent">
                <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3h18v4H3z"/>
                    <path d="M8 7v14"/>
                    <path d="M12 7v14"/>
                    <path d="M16 7v14"/>
                </svg>
                Reports
            </a>
            <div class="inline-flex rounded-md border">
                <button type="button" wire:click="setViewMode('card')"
                        class="px-3 py-2 text-sm font-medium rounded-l-md focus:outline-none focus:ring-2 focus:ring-ring"
                        :class="{ 'bg-accent text-accent-foreground': $wire.viewMode === 'card' }">
                    <x-ui.icon name="grid-3x3" class="mr-2 h-4 w-4" />
                    Cards
                </button>
                <button type="button" wire:click="setViewMode('list')"
                        class="px-3 py-2 text-sm font-medium rounded-r-md focus:outline-none focus:ring-2 focus:ring-ring border-l"
                        :class="{ 'bg-accent text-accent-foreground': $wire.viewMode === 'list' }">
                    <x-ui.icon name="list" class="mr-2 h-4 w-4" />
                    List
                </button>
            </div>
            <x-ui.button wire:click="openCreateModal">
                <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                    <path d="M12 5v14"/>
                </svg>
                Add Asset
            </x-ui.button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            <div class="flex">
                <svg class="h-4 w-4 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <x-ui.card class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Search</label>
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search assets..." class="mt-1.5" />
            </div>
            
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Category</label>
                <select wire:model.live="categoryFilter"
                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Status</label>
                <select wire:model.live="statusFilter"
                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="condemn">Condemn</option>
                    <option value="disposed">Disposed</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Recent</label>
                <select wire:model.live="recentFilter"
                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">All Time</option>
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>

            @php $user = auth()->user(); @endphp
            @if($user->isMainBranch() && $user->isPropertyOfficer() && count($branches) > 1)
            {{-- Property officer at main library gets toggle and branch filter --}}
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 mb-2 block">View Scope</label>
                <div class="mt-1.5 flex items-center gap-3 p-2 border border-input rounded-md bg-background">
                    <span class="text-sm {{ $branchFilter != $user->branch_id ? 'font-medium' : 'text-muted-foreground' }}">All Branches</span>
                    <button wire:click="toggleScope" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $branchFilter == $user->branch_id ? 'bg-blue-600' : 'bg-gray-200' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $branchFilter == $user->branch_id ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                    <span class="text-sm {{ $branchFilter == $user->branch_id ? 'font-medium' : 'text-muted-foreground' }}">Main Library Only</span>
                </div>
            </div>
            @if($branchFilter != $user->branch_id)
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Branch</label>
                <select wire:model.live="branchFilter"
                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @elseif(count($branches) > 1)
            {{-- Other users get standard branch filter --}}
            <div>
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Branch</label>
                <select wire:model.live="branchFilter"
                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
        
        <div class="mt-4 flex justify-between items-center">
            <p class="text-sm text-muted-foreground">
                Showing {{ $groups->count() }} of {{ $groups->total() }} groups
            </p>
            
            @if($search || $categoryFilter || $statusFilter || $branchFilter || $recentFilter)
                <x-ui.button wire:click="resetFilters" variant="outline" size="sm">
                    <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                    </svg>
                    Reset Filters
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>

    <!-- Status Legend -->
    <div class="flex items-center justify-center gap-6 py-3 px-4 bg-muted/30 rounded-lg border border-dashed">
        <div class="flex items-center gap-2 text-sm text-muted-foreground">
            <span class="font-medium">Status Legend:</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-green-700 bg-green-100 rounded-full">A</span>
            <span class="text-sm text-muted-foreground">Active</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-yellow-700 bg-yellow-100 rounded-full">C</span>
            <span class="text-sm text-muted-foreground">Condemn</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-red-700 bg-red-100 rounded-full">D</span>
            <span class="text-sm text-muted-foreground">Disposed</span>
        </div>
    </div>

    <!-- Assets Grid -->
    @if($groups->count() > 0)
        @if($viewMode === 'card')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($groups as $group)
                                                @php
                                                    $singleId = $group->assets->first()->id ?? null;
                                                    $isSingle = ($group->assets->count() === 1) && $singleId;
                                                    $singleStatus = $isSingle ? ($group->assets->first()->status ?? $group->status) : null;
                                                @endphp
                                <x-ui.card
                                    class="p-6 hover:shadow-md transition-shadow cursor-pointer"
                                    wire:click="{{ $isSingle ? 'openDetailsModal('.($singleId ?? 'null').')' : 'openGroupModal('.$group->id.')' }}"
                                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-2">
                            @if($isSingle)
                                @php
                                    $statusLetter = match($singleStatus) {
                                        'active' => 'A',
                                        'condemn' => 'C',
                                        'disposed' => 'D',
                                        default => 'A'
                                    };
                                    $statusColor = match($singleStatus) {
                                        'active' => 'text-green-700 bg-green-100',
                                        'condemn' => 'text-yellow-700 bg-yellow-100',
                                        'disposed' => 'text-red-700 bg-red-100',
                                        default => 'text-green-700 bg-green-100'
                                    };
                                @endphp
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full {{ $statusColor }}">
                                    {{ $statusLetter }}
                                </span>
                            @else
                                <span class="text-xs font-medium text-muted-foreground">
                                    <span class="inline-flex items-center justify-center w-6 h-6 mr-1 text-xs font-bold text-green-700 bg-green-100 rounded-full">A</span>{{ $group->active_count ?? 0 }}
                                    <span class="inline-flex items-center justify-center w-6 h-6 mx-1 text-xs font-bold text-yellow-700 bg-yellow-100 rounded-full">C</span>{{ $group->condemn_count ?? 0 }}
                                    <span class="inline-flex items-center justify-center w-6 h-6 ml-1 text-xs font-bold text-red-700 bg-red-100 rounded-full">D</span>{{ $group->disposed_count ?? 0 }}
                                </span>
                            @endif
                        </div>
                        <span class="text-xs font-medium bg-muted text-muted-foreground px-2 py-1 rounded">
                            {{ $group->items_count }} {{ $group->items_count === 1 ? 'item' : 'items' }}
                        </span>
                    </div>

                    @if($group->image_path)
                        <div class="mb-4 relative w-full h-32 overflow-hidden rounded-md border bg-muted">
                            <img src="{{ Storage::url($group->image_path) }}" 
                                 alt="{{ $group->description }}"
                                 class="absolute inset-0 h-full w-full object-cover" />
                        </div>
                    @endif

                    <div class="space-y-2">
                        <h3 class="font-semibold text-lg leading-tight">{{ $group->description }}</h3>
                        <p class="text-sm text-muted-foreground">{{ $group->category->name ?? '—' }}</p>
                        <p class="text-xs text-muted-foreground">Acquired: {{ \Carbon\Carbon::parse($group->date_acquired)->format('M d, Y') }}</p>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        @else
        <div class="overflow-x-hidden">
            <table class="w-full table-fixed divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset Group</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($groups as $group)
                                                            @php
                                                                $singleId = $group->assets->first()->id ?? null;
                                                                $isSingle = ($group->assets->count() === 1) && $singleId;
                                                                $singleStatus = $isSingle ? ($group->assets->first()->status ?? $group->status) : null;
                                                            @endphp
                                        <tr
                                            class="hover:bg-gray-50 cursor-pointer"
                                            wire:click="{{ $isSingle ? 'openDetailsModal('.($singleId ?? 'null').')' : 'openGroupModal('.$group->id.')' }}"
                                        >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($group->image_path)
                                    <div class="h-10 w-10 rounded-md overflow-hidden bg-muted border relative">
                                        <img src="{{ Storage::url($group->image_path) }}" alt="{{ $group->description }}" class="absolute inset-0 h-full w-full object-cover">
                                    </div>
                                @else
                                    <div class="h-10 w-10 rounded-md bg-muted border"></div>
                                @endif
                                <div>
                                    <div class="font-medium truncate max-w-[42ch]">{{ $group->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 truncate max-w-[20ch]">{{ $group->category->name ?? '—' }}</td>
                                                                        <td class="px-4 py-3 text-sm">
                                                                            {{ $group->items_count }}
                                                                            <span class="ml-2 text-xs text-muted-foreground">
                                                                                @if($isSingle)
                                                                                    @php
                                                                                        $counts = [
                                                                                            'active' => $singleStatus === 'active' ? 1 : 0,
                                                                                            'condemn' => $singleStatus === 'condemn' ? 1 : 0,
                                                                                            'disposed' => $singleStatus === 'disposed' ? 1 : 0,
                                                                                        ];
                                                                                    @endphp
                                                                                    <span class="text-green-700">A: {{ $counts['active'] }}</span>
                                                                                    <span class="text-yellow-700">• C: {{ $counts['condemn'] }}</span>
                                                                                    <span class="text-red-700">• D: {{ $counts['disposed'] }}</span>
                                                                                @else
                                                                                    <span class="text-green-700">A: {{ $group->active_count ?? 0 }}</span>
                                                                                    <span class="text-yellow-700">• C: {{ $group->condemn_count ?? 0 }}</span>
                                                                                    <span class="text-red-700">• D: {{ $group->disposed_count ?? 0 }}</span>
                                                                                @endif
                                                                            </span>
                                                                        </td>
                                                <td class="px-4 py-3 text-xs text-muted-foreground">
                                                    @if($isSingle)
                                                        @php
                                                            $statusLetter = match($singleStatus) {
                                                                'active' => 'A',
                                                                'condemn' => 'C',
                                                                'disposed' => 'D',
                                                                default => 'A'
                                                            };
                                                            $statusColor = match($singleStatus) {
                                                                'active' => 'bg-green-100 text-green-800',
                                                                'condemn' => 'bg-yellow-100 text-yellow-800',
                                                                'disposed' => 'bg-red-100 text-red-800',
                                                                default => 'bg-green-100 text-green-800'
                                                            };
                                                        @endphp
                                                        <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full {{ $statusColor }}">
                                                            {{ $statusLetter }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs">Multiple</span>
                                                    @endif
                                                </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                                        <div class="flex justify-end gap-2">
                                                                @if($isSingle)
                                                                    <button wire:click.stop="openDetailsModal({{ $singleId }})" class="inline-flex items-center justify-center rounded-md h-8 px-3 hover:bg-accent">View Details</button>
                                                                @else
                                                                    <button wire:click.stop="openGroupModal({{ $group->id }})" class="inline-flex items-center justify-center rounded-md h-8 px-3 hover:bg-accent">View Items</button>
                                                                @endif
                                                        </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Pagination -->
        <div class="mt-6">
            {{ $groups->links('pagination::custom-light') }}
        </div>
    @else
        <x-ui.card class="p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-muted">
                <svg class="h-6 w-6 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                    <line x1="16" x2="16" y1="2" y2="6"/>
                    <line x1="8" x2="8" y1="2" y2="6"/>
                    <line x1="3" x2="21" y1="10" y2="10"/>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold">No assets found</h3>
            <p class="mb-4 mt-2 text-sm text-muted-foreground">
                @if($search || $categoryFilter || $statusFilter || $branchFilter || $showMainLibraryOnly)
                    No assets match your current filters. Try adjusting your search criteria.
                @else
                    Get started by adding your first asset to the system.
                @endif
            </p>
            @if($search || $categoryFilter || $statusFilter || $branchFilter)
                <x-ui.button wire:click="resetFilters" variant="outline">Clear Filters</x-ui.button>
            @else
                <x-ui.button wire:click="openCreateModal">Add Your First Asset</x-ui.button>
            @endif
        </x-ui.card>
    @endif

        <!-- Group Details Modal (lists per-item property numbers) -->
        <div x-data="{ show: @entangle('showGroupModal') }"
             x-show="show"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     @click="$wire.closeGroupModal()"></div>

                <!-- Modal panel -->
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900" x-text="$wire.selectedGroup?.description">Asset Group</h3>
                            <p class="text-sm text-muted-foreground" x-text="$wire.selectedGroup?.category"></p>
                        </div>
                        <button @click="$wire.closeGroupModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-4">
                        <div class="text-sm text-muted-foreground">Items in this group</div>

                        <div class="rounded-md border overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">Property #</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Status</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="item in $wire.selectedGroupItems" :key="item.id">
                                        <tr class="hover:bg-accent/50 cursor-pointer" @click="$wire.openDetailsModal(item.id)">
                                            <td class="px-4 py-2 font-mono text-xs" x-text="item.property_number"></td>
                                            <td class="px-4 py-2 text-xs text-muted-foreground" x-text="`${item.branch ?? '—'} • ${item.division ?? '—'} • ${item.section ?? '—'}`"></td>
                                            <td class="px-4 py-2 text-xs">
                                                <span class="px-1.5 py-0.5 rounded"
                                                      :class="{
                                                        'bg-green-100 text-green-800': item.status === 'active',
                                                        'bg-yellow-100 text-yellow-800': item.status === 'condemn',
                                                        'bg-red-100 text-red-800': item.status === 'disposed'
                                                      }"
                                                      x-text="item.status ? (item.status.charAt(0).toUpperCase() + item.status.slice(1)) : '—'"></span>
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                <div class="inline-flex gap-2">
                                                    <button @click.stop="$wire.history(item.id)" class="inline-flex items-center justify-center rounded-md h-8 w-8 hover:bg-accent">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                                            <path d="M3 3v5h5"/>
                                                            <path d="M12 7v5l4 2"/>
                                                        </svg>
                                                    </button>
                                                    <button @click.stop="$wire.openEditModal(item.id)" class="inline-flex items-center justify-center rounded-md h-8 w-8 hover:bg-accent">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M4 13.5V4a2 2 0 0 1 2-2h8.5L20 7.5V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6.5"/>
                                                            <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                                            <path d="M10.42 12.61a2.1 2.1 0 1 1 2.97 2.97L7.95 21 4 22l1.05-3.95 5.37-5.44Z"/>
                                                        </svg>
                                                    </button>
                                                    <button @click.stop="$wire.transfer(item.id)" class="inline-flex items-center justify-center rounded-md h-8 w-8 hover:bg-accent">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Details Modal (legacy, still available if needed) -->
        <!-- Asset Details Modal -->
        <div x-data="{ show: @entangle('showDetailsModal') }"
             x-show="show"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     @click="$wire.closeDetailsModal()"></div>

                <!-- Modal panel -->
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Asset Details</h3>
                            <p class="text-sm text-muted-foreground" x-text="$wire.selectedAsset?.property_number"></p>
                        </div>
                        <button @click="$wire.closeDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1">
                                <div class="border rounded-lg p-2 bg-muted flex items-center justify-center min-h-40">
                                    <template x-if="$wire.selectedAsset?.image_url">
                                        <img :src="$wire.selectedAsset.image_url" alt="Asset image" class="rounded-md object-cover max-h-60">
                                    </template>
                                    <template x-if="!$wire.selectedAsset?.image_url">
                                        <div class="text-center text-muted-foreground text-sm">No image</div>
                                    </template>
                                </div>
                            </div>
                            <div class="md:col-span-2 space-y-4">
                                <div>
                                    <h4 class="font-semibold text-xl" x-text="$wire.selectedAsset?.description"></h4>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="text-xs font-mono text-muted-foreground bg-muted px-2 py-1 rounded" x-text="$wire.selectedAsset?.property_number"></span>
                                        <span class="text-xs inline-flex items-center rounded px-2 py-1"
                                              :class="{
                                                'bg-green-100 text-green-800': $wire.selectedAsset?.status === 'active',
                                                'bg-yellow-100 text-yellow-800': $wire.selectedAsset?.status === 'condemn',
                                                'bg-red-100 text-red-800': $wire.selectedAsset?.status === 'disposed'
                                              }"
                                              x-text="$wire.selectedAsset?.status?.charAt(0).toUpperCase() + $wire.selectedAsset?.status?.slice(1)"></span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="text-sm">
                                        <div class="text-muted-foreground">Category</div>
                                        <div class="font-medium" x-text="$wire.selectedAsset?.category"></div>
                                    </div>
                                    <div class="text-sm">
                                        <div class="text-muted-foreground">Date Acquired</div>
                                        <div class="font-medium" x-text="$wire.selectedAsset?.date_acquired_human"></div>
                                    </div>
                                    <div class="text-sm">
                                        <div class="text-muted-foreground">Quantity</div>
                                        <div class="font-medium" x-text="$wire.selectedAsset?.quantity"></div>
                                    </div>
                                    <div class="text-sm">
                                        <div class="text-muted-foreground">Unit Cost</div>
                                        <div class="font-medium" x-text="($wire.selectedAsset?.unit_cost ?? 0).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })"></div>
                                    </div>
                                    <div class="text-sm">
                                        <div class="text-muted-foreground">Total Cost</div>
                                        <div class="font-medium" x-text="($wire.selectedAsset?.total_cost ?? 0).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })"></div>
                                    </div>
                                    <div class="text-sm">
                                        <div class="text-muted-foreground">Source</div>
                                        <div class="font-medium" x-text="$wire.selectedAsset?.source === 'qc_property' ? 'QC Property' : 'Donation'"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="text-sm">
                                        <div class="text-muted-foreground">Branch</div>
                                        <div class="font-medium" x-text="$wire.selectedAsset?.branch"></div>
                                    </div>
                                    <div class="text-sm">
                                        <div class="text-muted-foreground">Division</div>
                                        <div class="font-medium" x-text="$wire.selectedAsset?.division"></div>
                                    </div>
                                    <div class="text-sm">
                                        <div class="text-muted-foreground">Section</div>
                                        <div class="font-medium" x-text="$wire.selectedAsset?.section"></div>
                                    </div>
                                </div>

                                <div class="pt-4 flex gap-2">
                                    <x-ui.button size="sm" @click="$wire.closeDetailsModal(); $wire.history($wire.selectedAsset?.id)">
                                        <svg class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                            <path d="M3 3v5h5"/>
                                            <path d="M12 7v5l4 2"/>
                                        </svg>
                                        View History
                                    </x-ui.button>
                                    <x-ui.button size="sm" variant="secondary" @click="$wire.closeDetailsModal(); $wire.openEditModal($wire.selectedAsset?.id)">
                                        <svg class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 13.5V4a2 2 0 0 1 2-2h8.5L20 7.5V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6.5"/>
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                            <path d="M10.42 12.61a2.1 2.1 0 1 1 2.97 2.97L7.95 21 4 22l1.05-3.95 5.37-5.44Z"/>
                                        </svg>
                                        Edit Asset
                                    </x-ui.button>
                                    <x-ui.button size="sm" variant="outline" @click="$wire.closeDetailsModal(); $wire.transfer($wire.selectedAsset?.id)">
                                        <svg class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                                        </svg>
                                        Transfer
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Asset Form Modal -->
    <div x-data="{ show: @entangle('showModal') }" 
         x-show="show" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="show" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 @click="$wire.closeModal()"></div>

            <!-- Modal panel -->
            <div x-show="show" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ $modalTitle }}</h3>
                    <button @click="$wire.closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
        <form wire:submit="save">
            <div class="px-6 py-4 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <!-- Property Number -->
                        <div>
                            <x-ui.label for="property_number" required>Property Number</x-ui.label>
                            <x-ui.input 
                                id="property_number" 
                                wire:model="property_number" 
                                readonly 
                                class="bg-muted"
                            />
                            @error('property_number') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <x-ui.label for="description" required>Description</x-ui.label>
                            <x-ui.textarea 
                                id="description" 
                                wire:model="description" 
                                rows="3"
                                placeholder="Enter asset description..."
                            />
                            @error('description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Category -->
                        <div>
                            <x-ui.label for="category_id" required>Category</x-ui.label>
                            <x-ui.select id="category_id" wire:model="category_id">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('category_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Quantity and Cost -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-ui.label for="quantity" required>Quantity</x-ui.label>
                                <x-ui.input 
                                    id="quantity" 
                                    type="number" 
                                    wire:model.live="quantity" 
                                    min="1"
                                />
                                @error('quantity') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <x-ui.label for="unit_cost" required>Unit Cost (₱)</x-ui.label>
                                <x-ui.input 
                                    id="unit_cost" 
                                    type="number" 
                                    step="0.01" 
                                    wire:model.live="unit_cost" 
                                    min="0"
                                />
                                @error('unit_cost') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Total Cost -->
                        <div>
                            <x-ui.label for="total_cost">Total Cost (₱)</x-ui.label>
                            <x-ui.input 
                                id="total_cost" 
                                wire:model="total_cost" 
                                readonly 
                                class="bg-muted"
                            />
                        </div>

                        <!-- Date Acquired -->
                        <div>
                            <x-ui.label for="date_acquired" required>Date Acquired</x-ui.label>
                            <x-ui.input 
                                id="date_acquired" 
                                type="date" 
                                wire:model="date_acquired"
                            />
                            @error('date_acquired') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <!-- Status and Source -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-ui.label for="status" required>Status</x-ui.label>
                                <x-ui.select id="status" wire:model="status">
                                    <option value="active">Active</option>
                                    <option value="condemn">Condemn</option>
                                    <option value="disposed">Disposed</option>
                                </x-ui.select>
                                @error('status') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <x-ui.label for="source" required>Source</x-ui.label>
                                <x-ui.select id="source" wire:model="source">
                                    <option value="qc_property">QC Property</option>
                                    <option value="donation">Donation</option>
                                </x-ui.select>
                                @error('source') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <x-ui.label for="current_branch_id" required>Branch</x-ui.label>
                            <x-ui.select id="current_branch_id" wire:model.live="current_branch_id">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('current_branch_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <x-ui.label for="current_division_id" required>Division</x-ui.label>
                            <x-ui.select id="current_division_id" wire:model.live="current_division_id">
                                <option value="">Select Division</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('current_division_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <x-ui.label for="current_section_id" required>Section</x-ui.label>
                            <x-ui.select id="current_section_id" wire:model="current_section_id">
                                <option value="">Select Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('current_section_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Image Upload -->
                        <div x-data>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Asset Image</label>
                            
                            <!-- Image Preview -->
                            @if($image)
                                <div class="mb-3">
                                    <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="mx-auto h-32 w-32 object-cover rounded-lg border">
                                    <div class="mt-2 flex items-center gap-3 justify-center">
                                        <x-ui.button type="button" size="sm" variant="secondary" @click="document.getElementById('image-upload').click()">Replace Image</x-ui.button>
                                        <button wire:click="$set('image', null)" type="button" class="text-sm text-red-600 hover:text-red-800">Remove Image</button>
                                    </div>
                                </div>
                            @elseif($current_image_path)
                                <div class="mb-3">
                                    <img src="{{ asset('storage/' . $current_image_path) }}" alt="Current image" class="mx-auto h-32 w-32 object-cover rounded-lg border">
                                    <div class="mt-2 flex items-center gap-3 justify-center">
                                        <x-ui.button type="button" size="sm" variant="secondary" @click="document.getElementById('image-upload').click()">Replace Image</x-ui.button>
                                    </div>
                                </div>
                            @endif

                            <!-- File Upload (only when no image yet) -->
                            @if(!$image && !$current_image_path)
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <div wire:loading wire:target="image" class="text-blue-600">
                                        <svg class="mx-auto h-12 w-12 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="text-sm">Uploading...</p>
                                    </div>
                                    <div wire:loading.remove wire:target="image">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="image-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload a file</span>
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Hidden file input reused for Upload/Replace -->
                            <input id="image-upload" wire:model="image" type="file" class="sr-only" accept="image/*">
                            
                            @error('image') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <x-ui.button type="button" variant="outline" wire:click="closeModal">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit">
                    <div wire:loading wire:target="save" class="mr-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                    </div>
                    {{ $editingAsset ? 'Update Asset' : 'Create Asset' }}
                </x-ui.button>
            </div>
        </form>
            </div>
        </div>
    </div>
</div>
