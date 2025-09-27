<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetGroup;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use App\Models\AssetTransferHistory;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

#[Layout('layouts.app')]
class AssetForm extends Component
{
    use WithFileUploads;

    public $assetId;
    public $property_number;
    public $description;
    public $quantity = 1;
    public $date_acquired;
    public $unit_cost;
    public $total_cost;
    public $category_id;
    public $status = 'active';
    public $source = 'qc_property';
    public $current_branch_id;
    public $current_division_id;
    public $current_section_id;
    public $image;
    public $existing_image;

    public $divisions = [];
    public $sections = [];

    protected $rules = [
        'description' => 'required|string|max:255',
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

    public function mount($assetId = null)
    {
        $user = auth()->user();
        
        if ($assetId) {
            $asset = Asset::forUser($user)->findOrFail($assetId);
            $this->assetId = $asset->id;
            $this->property_number = $asset->property_number;
            $this->description = $asset->description;
            $this->quantity = $asset->quantity;
            $this->date_acquired = $asset->date_acquired ? Carbon::parse($asset->date_acquired)->format('Y-m-d') : '';
            $this->unit_cost = $asset->unit_cost;
            $this->total_cost = $asset->total_cost;
            $this->category_id = $asset->category_id;
            $this->status = $asset->status;
            $this->source = $asset->source;
            $this->current_branch_id = $asset->current_branch_id;
            $this->current_division_id = $asset->current_division_id;
            $this->current_section_id = $asset->current_section_id;
            $this->existing_image = $asset->image_path;
            
            $this->loadDivisions();
            $this->loadSections();
        } else {
            $this->property_number = Asset::generatePropertyNumber();
            $this->current_branch_id = $user->branch_id;
            $this->current_division_id = $user->division_id;
            $this->current_section_id = $user->section_id;
            $this->date_acquired = now()->format('Y-m-d');
            
            $this->loadDivisions();
            $this->loadSections();
        }
    }

    public function updatedQuantity()
    {
        $this->calculateTotalCost();
    }

    public function updatedUnitCost()
    {
        $this->calculateTotalCost();
    }

    public function updatedCurrentBranchId()
    {
        $this->current_division_id = '';
        $this->current_section_id = '';
        $this->divisions = [];
        $this->sections = [];
        $this->loadDivisions();
    }

    public function updatedCurrentDivisionId()
    {
        $this->current_section_id = '';
        $this->sections = [];
        $this->loadSections();
    }

    public function updatedImage()
    {
        $this->validate(['image' => 'image|max:2048']);
    }

    private function calculateTotalCost()
    {
        // Total cost is per-item cost; for multiple creation we still store per-item value
        if ($this->unit_cost !== null && $this->unit_cost !== '') {
            $this->total_cost = $this->unit_cost;
        }
    }

    private function loadDivisions()
    {
        if ($this->current_branch_id) {
            $this->divisions = Division::where('branch_id', $this->current_branch_id)
                                     ->where('is_active', true)
                                     ->orderBy('name')
                                     ->get();
        }
    }

    private function loadSections()
    {
        if ($this->current_division_id) {
            $this->sections = Section::where('division_id', $this->current_division_id)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->get();
        }
    }

    public function save()
    {
        $this->validate();

        // Prepare shared fields (for group) and per-item fields (for asset)
        $groupData = [
            'description'   => $this->description,
            'category_id'   => $this->category_id,
            'date_acquired' => $this->date_acquired,
            'unit_cost'     => $this->unit_cost,
            'status'        => $this->status,
            'source'        => $this->source,
            'created_by'    => auth()->id(),
        ];
        $assetLocData = [
            'current_branch_id'  => $this->current_branch_id,
            'current_division_id'=> $this->current_division_id,
            'current_section_id' => $this->current_section_id,
        ];

        if ($this->image) {
            // Delete old image if updating
            if ($this->existing_image) {
                Storage::disk('public')->delete($this->existing_image);
            }
            $storedPath = $this->image->store('assets', 'public');
            $this->existing_image = $storedPath;
            $groupData['image_path'] = $storedPath;
        }

        if ($this->assetId) {
            // Update existing asset
            $asset = Asset::findOrFail($this->assetId);

            // If location changed, write transfer history first
            $locationChanged = (
                (int) $asset->current_branch_id !== (int) $this->current_branch_id ||
                (int) $asset->current_division_id !== (int) $this->current_division_id ||
                (int) $asset->current_section_id !== (int) $this->current_section_id
            );

            if ($locationChanged) {
                AssetTransferHistory::create([
                    'asset_id' => $asset->id,
                    'transfer_date' => now(),
                    'origin_branch_id' => $asset->current_branch_id,
                    'origin_division_id' => $asset->current_division_id,
                    'origin_section_id' => $asset->current_section_id,
                    'previous_branch_id' => $asset->current_branch_id,
                    'previous_division_id' => $asset->current_division_id,
                    'previous_section_id' => $asset->current_section_id,
                    'current_branch_id' => $this->current_branch_id,
                    'current_division_id' => $this->current_division_id,
                    'current_section_id' => $this->current_section_id,
                    'remarks' => 'Location updated via asset edit form',
                    'transferred_by' => auth()->id(),
                ]);
            }
            // Enforce single-item model on legacy columns
            $updateData = array_merge($assetLocData, [
                'description' => $this->description,
                'quantity' => 1,
                'date_acquired' => $this->date_acquired,
                'unit_cost' => $this->unit_cost,
                'total_cost' => $this->unit_cost,
                'category_id' => $this->category_id,
                'status' => $this->status,
                'source' => $this->source,
            ]);
            if (!empty($this->existing_image)) {
                $updateData['image_path'] = $this->existing_image;
            }
            $asset->update($updateData);
            
            session()->flash('success', 'Asset updated successfully.');
            $this->dispatch('asset-updated');
        } else {
            // Create new asset(s)
            // Find or create the AssetGroup for these shared attributes
            $group = AssetGroup::firstOrCreate([
                'description' => $groupData['description'],
                'category_id' => $groupData['category_id'],
                'date_acquired' => $groupData['date_acquired'],
                'unit_cost' => $groupData['unit_cost'],
                'status' => $groupData['status'],
                'source' => $groupData['source'],
                'created_by' => $groupData['created_by'],
            ], [
                'image_path' => $groupData['image_path'] ?? null,
            ]);

            // If creating multiple physical items, create one row per item with unique property numbers
            $count = max(1, (int) $this->quantity);

            // Base per-item data
            $baseData = array_merge($assetLocData, [
                'description' => $this->description, // legacy
                'quantity' => 1,
                'date_acquired' => $this->date_acquired, // legacy
                'unit_cost' => $this->unit_cost, // legacy
                'total_cost' => $this->unit_cost, // legacy per-item
                'category_id' => $this->category_id, // legacy
                'status' => $this->status, // legacy
                'source' => $this->source, // legacy
                'created_by' => auth()->id(),
                'asset_group_id' => $group->id,
            ]);

            $imagePath = $this->existing_image ?? null;

            $created = 0;
            for ($i = 0; $i < $count; $i++) {
                $assetData = $baseData;
                $assetData['property_number'] = Asset::generatePropertyNumber();
                if ($imagePath) {
                    $assetData['image_path'] = $imagePath;
                }
                Asset::create($assetData);
                $created++;
            }

            session()->flash('success', $created > 1
                ? ("Created {$created} assets with unique property numbers.")
                : 'Asset created successfully.'
            );
            $this->dispatch('asset-created');
        }

        return redirect()->route('assets.index');
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
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isStaff())) {
            return Branch::where('is_active', true)->orderBy('name')->get();
        }
        return Branch::where('id', $user->branch_id)->get();
    }

    public function render()
    {
        return view('livewire.assets.asset-form', [
            'categories' => $this->categories,
            'branches' => $this->branches,
        ]);
    }
}