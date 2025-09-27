<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetGroup;
use App\Models\Category;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use App\Models\Division;
use App\Models\Section;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

#[Layout('layouts.app')]
class AssetList extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = '';
    public $branchFilter = '';
    public $recentFilter = '';
    public $perPage = 12;
    public $viewMode = 'card'; // 'card' | 'list'

    // Modal properties
    public $showModal = false;
    public $editingAsset = null;
    public $modalTitle = '';

    // Details modal properties
    public $showDetailsModal = false;
    public $selectedAsset = null; // array of asset details
    // Group details modal
    public $showGroupModal = false;
    public $selectedGroup = null; // array of group details
    public $selectedGroupItems = [];

    // Form properties
    public $property_number = '';
    public $description = '';
    public $quantity = 1;
    public $date_acquired = '';
    public $unit_cost = '';
    public $total_cost = '';
    public $category_id = '';
    public $status = 'active';
    public $source = 'qc_property';
    public $image;
    public $current_image_path = '';
    public $image_preview_url = '';
    public $current_branch_id = '';
    public $current_division_id = '';
    public $current_section_id = '';

    // Available options
    public $divisions = [];
    public $sections = [];

    public function mount()
    {
        // Initialize with current user's location if available
        $user = auth()->user();
        if ($user && $user->branch_id) {
            $this->current_branch_id = $user->branch_id;
            $this->current_division_id = $user->division_id;
            $this->current_section_id = $user->section_id;
        }
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'branchFilter' => ['except' => ''],
        'recentFilter' => ['except' => ''],
        'viewMode' => ['except' => 'card'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingBranchFilter()
    {
        $this->resetPage();
    }

    public function updatingRecentFilter()
    {
        $this->resetPage();
    }

    protected $rules = [
        'description' => 'required|string|max:1000',
        'quantity' => 'required|integer|min:1',
        'date_acquired' => 'required|date',
        'unit_cost' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'status' => 'required|in:active,condemn,disposed',
        'source' => 'required|in:qc_property,donation',
        'current_branch_id' => 'required|exists:branches,id',
        'current_division_id' => 'required|exists:divisions,id',
        'current_section_id' => 'required|exists:sections,id',
        'image' => 'nullable|image|max:2048',
    ];

    public function resetFilters()
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->statusFilter = '';
        $this->branchFilter = '';
        $this->recentFilter = '';
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalTitle = 'Add New Asset';
        $this->property_number = Asset::generatePropertyNumber();
        $this->date_acquired = now()->format('Y-m-d');
        
        // Set default location from current user
        $user = auth()->user();
        if ($user) {
            $this->current_branch_id = $user->branch_id ?? '';
            $this->current_division_id = $user->division_id ?? '';
            $this->current_section_id = $user->section_id ?? '';
        }
        
        $this->loadDivisions();
        $this->loadSections();
        $this->showModal = true;
    }

    public function openEditModal($assetId)
    {
        $this->resetForm();
        $this->editingAsset = Asset::findOrFail($assetId);
        $this->modalTitle = 'Edit Asset';
        
        $this->property_number = $this->editingAsset->property_number;
        $this->description = $this->editingAsset->description;
        $this->quantity = $this->editingAsset->quantity;
        $this->date_acquired = $this->editingAsset->date_acquired;
        $this->unit_cost = $this->editingAsset->unit_cost;
        $this->total_cost = $this->editingAsset->total_cost;
        $this->category_id = $this->editingAsset->category_id;
        $this->status = $this->editingAsset->status;
        $this->source = $this->editingAsset->source;
        $this->current_image_path = $this->editingAsset->image_path;
        $this->current_branch_id = $this->editingAsset->current_branch_id;
        $this->current_division_id = $this->editingAsset->current_division_id;
        $this->current_section_id = $this->editingAsset->current_section_id;
        
        $this->loadDivisions();
        $this->loadSections();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedAsset = null;
    }

    public function resetForm()
    {
        $this->editingAsset = null;
        $this->property_number = '';
        $this->description = '';
        $this->quantity = 1;
        $this->date_acquired = '';
        $this->unit_cost = '';
        $this->total_cost = '';
        $this->category_id = '';
        $this->status = 'active';
        $this->source = 'qc_property';
        $this->image = null;
        $this->current_image_path = '';
        $this->image_preview_url = '';
        $this->current_branch_id = '';
        $this->current_division_id = '';
        $this->current_section_id = '';
        $this->divisions = [];
        $this->sections = [];
        $this->resetErrorBag();
    }

    public function updatedUnitCost()
    {
        $this->calculateTotalCost();
    }

    public function updatedQuantity()
    {
        $this->calculateTotalCost();
    }

    public function calculateTotalCost()
    {
        if ($this->unit_cost && $this->quantity) {
            $this->total_cost = number_format($this->unit_cost * $this->quantity, 2, '.', '');
        }
    }

    public function updatedCurrentBranchId()
    {
        $this->current_division_id = '';
        $this->current_section_id = '';
        $this->sections = [];
        $this->loadDivisions();
    }

    public function updatedCurrentDivisionId()
    {
        $this->current_section_id = '';
        $this->loadSections();
    }

    public function updatedImage()
    {
        // Validate the uploaded image
        $this->validate(['image' => 'image|max:2048']);
        
        // Generate temporary URL for preview
        if ($this->image) {
            $this->image_preview_url = $this->image->temporaryUrl();
        }
    }

    public function loadDivisions()
    {
        if ($this->current_branch_id) {
            $this->divisions = Division::where('branch_id', $this->current_branch_id)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->get();
        } else {
            $this->divisions = [];
        }
    }

    public function loadSections()
    {
        if ($this->current_division_id) {
            $this->sections = Section::where('division_id', $this->current_division_id)
                                   ->where('is_active', true)
                                   ->orderBy('name')
                                   ->get();
        } else {
            $this->sections = [];
        }
    }

    public function save()
    {
        $this->validate();

        // Build shared/group data
        $groupPayload = [
            'description' => $this->description,
            'category_id' => $this->category_id,
            'date_acquired' => $this->date_acquired,
            'unit_cost' => $this->unit_cost,
            'status' => $this->status,
            'source' => $this->source,
            'created_by' => auth()->id(),
        ];
        $locPayload = [
            'current_branch_id' => $this->current_branch_id,
            'current_division_id' => $this->current_division_id,
            'current_section_id' => $this->current_section_id,
        ];

        // Handle image upload
        $imagePath = null;
        if ($this->image) {
            if ($this->editingAsset && $this->editingAsset->image_path) {
                Storage::disk('public')->delete($this->editingAsset->image_path);
            }
            $imagePath = $this->image->store('assets', 'public');
        }

        if ($this->editingAsset) {
            // Update existing asset (per-item semantics)
            $update = array_merge($locPayload, [
                'description' => $this->description,
                'quantity' => 1,
                'date_acquired' => $this->date_acquired,
                'unit_cost' => $this->unit_cost,
                'total_cost' => $this->unit_cost,
                'category_id' => $this->category_id,
                'status' => $this->status,
                'source' => $this->source,
            ]);
            if ($imagePath) { $update['image_path'] = $imagePath; }
            $this->editingAsset->update($update);
            
            session()->flash('success', 'Asset updated successfully!');
            $this->dispatch('asset-updated');
        } else {
            // Find or create group
            $group = AssetGroup::firstOrCreate([
                'description' => $groupPayload['description'],
                'category_id' => $groupPayload['category_id'],
                'date_acquired' => $groupPayload['date_acquired'],
                'unit_cost' => $groupPayload['unit_cost'],
                'status' => $groupPayload['status'],
                'source' => $groupPayload['source'],
                'created_by' => $groupPayload['created_by'],
            ], [
                'image_path' => $imagePath,
            ]);

            // Create N per-item asset rows
            $n = max(1, (int) $this->quantity);
            for ($i = 0; $i < $n; $i++) {
                $payload = array_merge($locPayload, [
                    'property_number' => Asset::generatePropertyNumber(),
                    'asset_group_id' => $group->id,
                    // legacy columns kept during staged migration
                    'description' => $this->description,
                    'quantity' => 1,
                    'date_acquired' => $this->date_acquired,
                    'unit_cost' => $this->unit_cost,
                    'total_cost' => $this->unit_cost,
                    'category_id' => $this->category_id,
                    'status' => $this->status,
                    'source' => $this->source,
                    'created_by' => auth()->id(),
                ]);
                if ($imagePath) { $payload['image_path'] = $imagePath; }
                Asset::create($payload);
            }

            session()->flash('success', $n > 1
                ? ("Created {$n} assets with unique property numbers under one group.")
                : 'Asset created successfully!');
            $this->dispatch('asset-created');
        }

        $this->closeModal();
    }

    public function openDetailsModal($assetId)
    {
        // Load asset with relationships, scoping by user
        $asset = Asset::with(['category', 'currentBranch', 'currentDivision', 'currentSection', 'createdBy'])
            ->forUser(auth()->user())
            ->findOrFail($assetId);

        $this->selectedAsset = [
            'id' => $asset->id,
            'property_number' => $asset->property_number,
            'description' => $asset->description,
            'quantity' => $asset->quantity,
            'unit_cost' => (float) $asset->unit_cost,
            'total_cost' => (float) $asset->total_cost,
            'date_acquired' => $asset->date_acquired ? Carbon::parse($asset->date_acquired)->format('Y-m-d') : null,
            'date_acquired_human' => $asset->date_acquired ? Carbon::parse($asset->date_acquired)->format('M d, Y') : null,
            'category' => $asset->category?->name,
            'status' => $asset->status,
            'source' => $asset->source,
            'image_url' => $asset->image_path ? Storage::url($asset->image_path) : null,
            'branch' => $asset->currentBranch?->name,
            'division' => $asset->currentDivision?->name,
            'section' => $asset->currentSection?->name,
            'created_by' => $asset->createdBy?->name,
        ];

        $this->showDetailsModal = true;
    }

    public function edit($assetId)
    {
        return redirect()->route('assets.form', $assetId);
    }

    public function transfer($assetId)
    {
        return redirect()->route('assets.transfer', $assetId);
    }

    public function history($assetId)
    {
        return redirect()->route('assets.history', $assetId);
    }

    public function setViewMode($mode)
    {
        if (in_array($mode, ['card', 'list'], true)) {
            $this->viewMode = $mode;
        }
    }

    #[On('asset-created')]
    #[On('asset-updated')]
    #[On('asset-transferred')]
    public function refreshAssets()
    {
        $this->resetPage();
    }

    // Legacy per-item listing (kept for reference, now unused by view when grouping enabled)
    public function getAssetsProperty()
    {
        $query = Asset::with(['category', 'currentBranch', 'currentDivision', 'currentSection'])
            ->forUser(auth()->user());

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('property_number', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->branchFilter) {
            $query->where('current_branch_id', $this->branchFilter);
        }

        if ($this->recentFilter) {
            $days = (int) $this->recentFilter;
            if ($days > 0) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($this->perPage);
    }

    // New: Grouped listing sourced from AssetGroup with aggregated items
    public function getGroupsProperty()
    {
        $user = auth()->user();
        $q = AssetGroup::query()
            ->with(['category'])
            // Total items count in scope (by branch + user) regardless of status
            ->withCount(['assets as items_count' => function ($aq) use ($user) {
                $aq->forUser($user);
                if ($this->branchFilter) {
                    $aq->where('current_branch_id', (int) $this->branchFilter);
                }
            }])
            // Per-status counts to display breakdown
            ->withCount([
                'assets as active_count' => function ($aq) use ($user) {
                    $aq->forUser($user)->where('status', 'active');
                    if ($this->branchFilter) {
                        $aq->where('current_branch_id', (int) $this->branchFilter);
                    }
                },
                'assets as condemn_count' => function ($aq) use ($user) {
                    $aq->forUser($user)->where('status', 'condemn');
                    if ($this->branchFilter) {
                        $aq->where('current_branch_id', (int) $this->branchFilter);
                    }
                },
                'assets as disposed_count' => function ($aq) use ($user) {
                    $aq->forUser($user)->where('status', 'disposed');
                    if ($this->branchFilter) {
                        $aq->where('current_branch_id', (int) $this->branchFilter);
                    }
                },
            ])
            ->with(['assets' => function ($aq) use ($user) {
                $aq->forUser($user)
                   ->select('id','asset_group_id','property_number','current_branch_id','current_division_id','current_section_id','status');
                if ($this->statusFilter) {
                    $aq->where('status', $this->statusFilter);
                }
                if ($this->branchFilter) {
                    $aq->where('current_branch_id', (int) $this->branchFilter);
                }
            }]);

        // Apply filters on group fields + whereHas on assets for branch/recent scoping
        if ($this->search) {
            $term = '%'.$this->search.'%';
            $q->where(function ($w) use ($term) {
                $w->where('description', 'like', $term);
            })->orWhereHas('assets', function ($aq) use ($term) {
                $aq->where('property_number', 'like', $term);
            });
        }

        if ($this->categoryFilter) {
            $q->where('category_id', $this->categoryFilter);
        }

        if ($this->statusFilter) {
            // Filter groups that have at least one asset with the selected status
            $status = $this->statusFilter;
            $q->whereHas('assets', function ($aq) use ($status, $user) {
                $aq->forUser($user)->where('status', $status);
            });
        }

        if ($this->branchFilter) {
            $branchId = (int) $this->branchFilter;
            $q->whereHas('assets', function ($aq) use ($branchId) {
                $aq->where('current_branch_id', $branchId);
            });
        } else {
            // Ensure scoping via assets relationship too
            $q->whereHas('assets', function ($aq) use ($user) {
                $aq->forUser($user);
            });
        }

        if ($this->recentFilter) {
            $days = (int) $this->recentFilter;
            if ($days > 0) {
                $q->where('created_at', '>=', now()->subDays($days));
            }
        }

        // Order by latest group creation
        $paginator = $q->orderByDesc('created_at')->paginate($this->perPage);
        return $paginator;
    }

    public function openGroupModal($groupId)
    {
        $user = auth()->user();
        $group = AssetGroup::with(['category'])
            ->with(['assets' => function ($aq) use ($user) {
                $aq->forUser($user)
                   ->with(['currentBranch','currentDivision','currentSection'])
                   ->when($this->statusFilter !== '', fn($w) => $w->where('status', $this->statusFilter))
                   ->when($this->branchFilter !== '', fn($w) => $w->where('current_branch_id', (int) $this->branchFilter))
                   ->orderBy('property_number');
            }])
            ->findOrFail($groupId);

        $this->selectedGroup = [
            'id' => $group->id,
            'description' => $group->description,
            'category' => $group->category?->name,
            'status' => $group->status,
            'date_acquired' => $group->date_acquired,
            'unit_cost' => (float) $group->unit_cost,
            'source' => $group->source,
            'image_path' => $group->image_path,
        ];

        $this->selectedGroupItems = $group->assets->map(function ($a) {
            return [
                'id' => $a->id,
                'property_number' => $a->property_number,
                'branch' => $a->currentBranch?->name,
                'division' => $a->currentDivision?->name,
                'section' => $a->currentSection?->name,
            ];
        })->values()->all();

        $this->showGroupModal = true;
    }

    public function closeGroupModal()
    {
        $this->showGroupModal = false;
        $this->selectedGroup = null;
        $this->selectedGroupItems = [];
    }

    public function getCategoriesProperty()
    {
        return Category::where('type', 'asset')
                      ->where('is_active', true)
                      ->orderBy('name')
                      ->get();
    }

    public function getBranchesProperty()
    {
        $user = auth()->user();
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver())) {
            return Branch::where('is_active', true)->orderBy('name')->get();
        }
        return Branch::where('id', $user->branch_id)->get();
    }

    public function render()
    {
        return view('livewire.assets.asset-list', [
            // For grouped UI, we pass groups. Existing blade will be updated accordingly.
            'groups' => $this->groups,
            'categories' => $this->categories,
            'branches' => $this->branches,
        ]);
    }
}