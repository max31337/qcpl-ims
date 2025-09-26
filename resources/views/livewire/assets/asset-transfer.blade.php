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
        <h1 class="text-3xl font-bold tracking-tight">Transfer Asset</h1>
        <p class="text-muted-foreground">Move this asset to a different location within the library system</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            <div class="flex">
                <svg class="h-4 w-4 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <div class="flex">
                <svg class="h-4 w-4 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Asset Information -->
        <x-ui.card>
            <div class="flex flex-col space-y-1.5 p-6 pb-4">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Asset Details</h3>
                <p class="text-sm text-muted-foreground">Information about the asset being transferred</p>
            </div>
            <div class="p-6 pt-0 space-y-4">
                @if($asset->image_path)
                    <div>
                        <img src="{{ Storage::url($asset->image_path) }}" 
                             alt="{{ $asset->description }}"
                             class="h-32 w-full rounded-md object-cover border" />
                    </div>
                @endif

                <div class="space-y-3">
                    <div>
                        <span class="text-xs font-mono text-muted-foreground bg-muted px-2 py-1 rounded">
                            {{ $asset->property_number }}
                        </span>
                    </div>

                    <div>
                        <h4 class="font-semibold text-lg">{{ $asset->description }}</h4>
                        <p class="text-sm text-muted-foreground">{{ $asset->category->name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Quantity:</span>
                            <span class="font-medium">{{ $asset->quantity }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Value:</span>
                            <span class="font-medium">₱{{ number_format($asset->total_cost, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Status:</span>
                            <x-ui.badge
                                :variant="$asset->status === 'active' ? 'success' : ($asset->status === 'condemn' ? 'warning' : 'danger')">
                                {{ ucfirst($asset->status) }}
                            </x-ui.badge>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Source:</span>
                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $asset->source)) }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <h5 class="font-medium mb-2">Current Location</h5>
                    <div class="text-sm text-muted-foreground space-y-1">
                        <div>📍 {{ $asset->currentBranch->name }}</div>
                        <div class="ml-4">🏢 {{ $asset->currentDivision->name }}</div>
                        <div class="ml-8">📂 {{ $asset->currentSection->name }}</div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- Transfer Form -->
        <x-ui.card>
            <div class="flex flex-col space-y-1.5 p-6 pb-4">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Transfer Destination</h3>
                <p class="text-sm text-muted-foreground">Select the new location for this asset</p>
            </div>
            <div class="p-6 pt-0">
                <form wire:submit="transfer" class="space-y-4">
                    <div>
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Target Branch <span class="text-destructive">*</span>
                        </label>
                        <select wire:model.live="target_branch_id" 
                                class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('target_branch_id') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Target Division <span class="text-destructive">*</span>
                        </label>
                        <select wire:model.live="target_division_id" 
                                class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="">Select Division</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                        @error('target_division_id') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Target Section <span class="text-destructive">*</span>
                        </label>
                        <select wire:model="target_section_id" 
                                class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                        @error('target_section_id') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Transfer Remarks
                        </label>
                        <textarea wire:model="remarks" 
                                  placeholder="Optional notes about this transfer"
                                  class="mt-1.5 flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></textarea>
                        @error('remarks') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if($target_branch_id && $target_division_id && $target_section_id)
                        <div class="p-4 bg-muted rounded-lg">
                            <h5 class="font-medium mb-2">Transfer Summary:</h5>
                            <div class="text-sm space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">From:</span>
                                    <span>{{ $asset->currentBranch->name }} → {{ $asset->currentDivision->name }} → {{ $asset->currentSection->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">To:</span>
                                    <span>
                                        @if($target_branch_id)
                                            {{ $branches->find($target_branch_id)?->name }}
                                        @endif
                                        @if($target_division_id)
                                            → {{ collect($divisions)->find($target_division_id)?->name }}
                                        @endif
                                        @if($target_section_id)  
                                            → {{ collect($sections)->find($target_section_id)?->name }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex gap-3 pt-4">
                        <x-ui.button type="submit" class="flex-1">
                            <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                            </svg>
                            Transfer Asset
                        </x-ui.button>
                        
                        <a href="{{ route('assets.index') }}" wire:navigate>
                            <x-ui.button variant="outline">Cancel</x-ui.button>
                        </a>
                    </div>
                </form>
            </div>
        </x-ui.card>
    </div>
</div>
