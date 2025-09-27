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
        <h1 class="text-3xl font-bold tracking-tight">
            {{ $assetId ? 'Edit Asset' : 'Add New Asset' }}
        </h1>
        <p class="text-muted-foreground">
            {{ $assetId ? 'Update asset information and details' : 'Create a new asset entry for the library system' }}
        </p>
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

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card>
                    <div class="flex flex-col space-y-1.5 p-6 pb-4">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">Asset Information</h3>
                        <p class="text-sm text-muted-foreground">Basic details about the asset</p>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Property Number
                                </label>
                                <x-ui.input wire:model="property_number" readonly class="mt-1.5 bg-muted" />
                                <p class="text-xs text-muted-foreground mt-1">Auto-generated system number</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Category <span class="text-destructive">*</span>
                                </label>
                                <select wire:model="category_id" 
                                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Description <span class="text-destructive">*</span>
                            </label>
                            <textarea wire:model="description" 
                                      placeholder="Enter detailed description of the asset"
                                      class="mt-1.5 flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></textarea>
                            @error('description') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Quantity <span class="text-destructive">*</span>
                                </label>
                                @if(!$assetId)
                                    <x-ui.input type="number" wire:model.live="quantity" min="1" class="mt-1.5" />
                                    <p class="text-xs text-muted-foreground mt-1">We'll create one record per item with its own Property Number.</p>
                                @else
                                    <x-ui.input type="number" value="1" readonly class="mt-1.5 bg-muted" />
                                    <p class="text-xs text-muted-foreground mt-1">Each asset record represents a single physical item.</p>
                                @endif
                                @error('quantity') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Unit Cost <span class="text-destructive">*</span>
                                </label>
                                <x-ui.input type="number" wire:model.live="unit_cost" step="0.01" min="0" class="mt-1.5" />
                                @error('unit_cost') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Total Cost (per item)
                                </label>
                                <x-ui.input wire:model="total_cost" readonly class="mt-1.5 bg-muted" />
                                <p class="text-xs text-muted-foreground mt-1">Auto-calculated</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Date Acquired <span class="text-destructive">*</span>
                                </label>
                                <x-ui.input type="date" wire:model="date_acquired" class="mt-1.5" />
                                @error('date_acquired') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Source <span class="text-destructive">*</span>
                                </label>
                                <select wire:model="source" 
                                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    <option value="qc_property">QC Property</option>
                                    <option value="donation">Donation</option>
                                </select>
                                @error('source') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Status <span class="text-destructive">*</span>
                            </label>
                            <select wire:model="status" 
                                    class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="active">Active</option>
                                <option value="condemn">Condemn</option>
                                <option value="disposed">Disposed</option>
                            </select>
                            @error('status') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-ui.card>

                <!-- Location Information -->
                <x-ui.card>
                    <div class="flex flex-col space-y-1.5 p-6 pb-4">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">Current Location</h3>
                        <p class="text-sm text-muted-foreground">Where this asset is currently located</p>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Branch <span class="text-destructive">*</span>
                            </label>
                            <select wire:model.live="current_branch_id" 
                                    class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('current_branch_id') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Division <span class="text-destructive">*</span>
                                </label>
                                <select wire:model.live="current_division_id" 
                                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    <option value="">Select Division</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                                @error('current_division_id') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Section <span class="text-destructive">*</span>
                                </label>
                                <select wire:model="current_section_id" 
                                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    <option value="">Select Section</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                                @error('current_section_id') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Image Upload -->
            <div class="space-y-6">
                <x-ui.card>
                    <div class="flex flex-col space-y-1.5 p-6 pb-4">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">Asset Image</h3>
                        <p class="text-sm text-muted-foreground">Upload a photo of the asset</p>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="space-y-4">
                            @if($existing_image && !$image)
                                <div class="space-y-2">
                                    <p class="text-sm font-medium">Current Image:</p>
                                    <img src="{{ Storage::url($existing_image) }}" 
                                         alt="Current asset image" 
                                         class="w-full h-48 object-cover rounded-md border">
                                </div>
                            @endif

                            @if($image)
                                <div class="space-y-2">
                                    <p class="text-sm font-medium">New Image Preview:</p>
                                    <img src="{{ $image->temporaryUrl() }}" 
                                         alt="New asset image" 
                                         class="w-full h-48 object-cover rounded-md border">
                                </div>
                            @endif

                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    {{ $existing_image && !$image ? 'Replace Image' : 'Upload Image' }}
                                </label>
                                <input type="file" 
                                       wire:model="image" 
                                       accept="image/*"
                                       class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                @error('image') <p class="text-sm text-destructive mt-1">{{ $message }}</p> @enderror
                                <p class="text-xs text-muted-foreground mt-1">Maximum file size: 2MB</p>
                            </div>

                            <div wire:loading wire:target="image" class="text-sm text-muted-foreground">
                                Uploading image...
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card class="p-6">
                    <div class="space-y-4">
                        <x-ui.button type="submit" class="w-full">
                            <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17,21 17,13 7,13 7,21"/>
                                <polyline points="7,3 7,8 15,8"/>
                            </svg>
                            {{ $assetId ? 'Update Asset' : 'Create Asset' }}
                        </x-ui.button>

                        <a href="{{ route('assets.index') }}" wire:navigate class="block">
                            <x-ui.button variant="outline" class="w-full">Cancel</x-ui.button>
                        </a>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
