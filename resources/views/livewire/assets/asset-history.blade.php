<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('assets.index') }}" wire:navigate>
                <x-ui.button variant="ghost" size="sm">
                    <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7"/>
                        <path d="m19 12-7 7-7-7"/>
                    </svg>
                    Back to Assets
                </x-ui.button>
            </a>
        </div>
        <h1 class="text-3xl font-bold tracking-tight">Asset Transfer History</h1>
        <p class="text-muted-foreground">Complete transfer history and location changes for this asset</p>
    </div>

    <!-- Asset Overview -->
    <x-ui.card class="mb-6">
        <div class="p-6">
            <div class="flex items-start gap-4">
                @if($asset->image_path)
                    <img src="{{ Storage::url($asset->image_path) }}" 
                         alt="{{ $asset->description }}"
                         class="h-20 w-20 rounded-md object-cover border flex-shrink-0" />
                @endif
                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-mono text-muted-foreground bg-muted px-2 py-1 rounded">
                            {{ $asset->property_number }}
                        </span>
                        <x-ui.badge
                            :variant="$asset->status === 'active' ? 'success' : ($asset->status === 'condemn' ? 'warning' : 'danger')">
                            {{ ucfirst($asset->status) }}
                        </x-ui.badge>
                    </div>
                    
                    <h3 class="font-semibold text-lg mb-1">{{ $asset->description }}</h3>
                    <p class="text-sm text-muted-foreground mb-2">{{ $asset->category->name }}</p>
                    
                    <div class="flex flex-col md:flex-row md:items-center md:gap-6 text-sm text-muted-foreground">
                        <span>
                            Origin:
                            @if(isset($originRecord) && $originRecord && $originRecord->originBranch)
                                {{ $originRecord->originBranch->name }} →
                                {{ $originRecord->originDivision->name }} →
                                {{ $originRecord->originSection->name }}
                            @else
                                {{ $asset->currentBranch->name }} → {{ $asset->currentDivision->name }} → {{ $asset->currentSection->name }}
                            @endif
                        </span>
                        <span>
                            Current: {{ $asset->currentBranch->name }} → {{ $asset->currentDivision->name }} → {{ $asset->currentSection->name }}
                        </span>
                        <span>Value: ₱{{ number_format($asset->total_cost, 2) }}</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    @can('update', $asset)
                        <a href="{{ route('assets.edit', $asset->id) }}" wire:navigate>
                            <x-ui.button variant="outline" size="sm">
                                <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 13.5V4a2 2 0 0 1 2-2h8.5L20 7.5V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6.5"/>
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                    <path d="M10.42 12.61a2.1 2.1 0 1 1 2.97 2.97L7.95 21 4 22l1.05-3.95 5.37-5.44Z"/>
                                </svg>
                                Edit
                            </x-ui.button>
                        </a>
                    @endcan
                    
                    @can('transfer', $asset)
                        <a href="{{ route('assets.transfer', $asset->id) }}" wire:navigate>
                            <x-ui.button size="sm">
                                <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                                </svg>
                                Transfer
                            </x-ui.button>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </x-ui.card>

    <!-- Transfer History -->
    <x-ui.card>
        <div class="flex flex-col space-y-1.5 p-6 pb-4">
            <h3 class="text-2xl font-semibold leading-none tracking-tight">
                Transfer History
                @if($transferHistory->total() > 0)
                    <span class="text-sm font-normal text-muted-foreground ml-2">
                        ({{ $transferHistory->total() }} {{ Str::plural('transfer', $transferHistory->total()) }})
                    </span>
                @endif
            </h3>
            <p class="text-sm text-muted-foreground">Complete history of all location changes for this asset</p>
        </div>
        
        <div class="p-6 pt-0">
            @if($transferHistory->count() > 0)
                <div class="space-y-4">
                    @foreach($transferHistory as $history)
                        <div class="relative border rounded-lg p-4 {{ $loop->first ? 'border-primary bg-primary/5' : 'border-border' }}">
                            @if(!$loop->last)
                                <div class="absolute left-6 top-16 bottom-0 w-px bg-border"></div>
                            @endif
                            
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full border-2 border-primary bg-background flex items-center justify-center">
                                        @if($loop->first)
                                            <svg class="h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                                <circle cx="12" cy="10" r="3"/>
                                            </svg>
                                        @else
                                            <div class="w-2 h-2 rounded-full bg-primary"></div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-sm">
                                                {{ $history->transfer_date->format('M j, Y') }}
                                            </span>
                                            @if($loop->first)
                                                <x-ui.badge variant="success" class="text-xs">Current</x-ui.badge>
                                            @endif
                                        </div>
                                        <span class="text-xs text-muted-foreground">
                                            {{ $history->created_at->format('g:i A') }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2 mb-3">
                                        @if($history->previousBranch)
                                            <div class="flex items-center gap-2 text-sm">
                                                <span class="text-muted-foreground">From:</span>
                                                <span class="font-medium">
                                                    {{ $history->previousBranch->name }} → 
                                                    {{ $history->previousDivision->name }} → 
                                                    {{ $history->previousSection->name }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="text-muted-foreground">To:</span>
                                            <span class="font-medium">
                                                {{ $history->currentBranch->name }} → 
                                                {{ $history->currentDivision->name }} → 
                                                {{ $history->currentSection->name }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($history->remarks)
                                        <div class="text-sm text-muted-foreground bg-muted/50 rounded p-2 mb-2">
                                            <strong>Remarks:</strong> {{ $history->remarks }}
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        <span>Transferred by {{ $history->transferredBy->name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($transferHistory->hasPages())
                    <div class="mt-6">
                        {{ $transferHistory->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-muted">
                        <svg class="h-6 w-6 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                            <path d="M12 7v5l4 2"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold">No Transfer History</h3>
                    <p class="mb-4 mt-2 text-sm text-muted-foreground">
                        This asset has not been transferred yet. It remains in its original location.
                    </p>
                    @can('transfer', $asset)
                        <a href="{{ route('assets.transfer', $asset->id) }}" wire:navigate>
                            <x-ui.button>Transfer Asset</x-ui.button>
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </x-ui.card>
</div>
