<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetTransferHistory;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AssetTransfer extends Component
{
    public $asset;
    public $target_branch_id;
    public $target_division_id;
    public $target_section_id;
    public $remarks;

    public $divisions = [];
    public $sections = [];

    protected $rules = [
        'target_branch_id' => 'required|exists:branches,id',
        'target_division_id' => 'required|exists:divisions,id',
        'target_section_id' => 'required|exists:sections,id',
        'remarks' => 'nullable|string|max:500',
    ];

    public function mount($assetId)
    {
        $this->asset = Asset::forUser(auth()->user())->findOrFail($assetId);
        
        // Can't transfer to same location
        $this->authorize('transfer', $this->asset);
    }

    public function updatedTargetBranchId()
    {
        $this->target_division_id = '';
        $this->target_section_id = '';
        $this->divisions = [];
        $this->sections = [];
        $this->loadDivisions();
    }

    public function updatedTargetDivisionId()
    {
        $this->target_section_id = '';
        $this->sections = [];
        $this->loadSections();
    }

    private function loadDivisions()
    {
        if ($this->target_branch_id) {
            $this->divisions = Division::where('branch_id', $this->target_branch_id)
                                     ->where('is_active', true)
                                     ->orderBy('name')
                                     ->get();
        }
    }

    private function loadSections()
    {
        if ($this->target_division_id) {
            $this->sections = Section::where('division_id', $this->target_division_id)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->get();
        }
    }

    public function transfer()
    {
        $this->validate();

        // Check if transferring to same location
        if ($this->asset->current_branch_id == $this->target_branch_id &&
            $this->asset->current_division_id == $this->target_division_id &&
            $this->asset->current_section_id == $this->target_section_id) {
            session()->flash('error', 'Cannot transfer to the same location.');
            return;
        }

        try {
            // Create transfer history record
            AssetTransferHistory::create([
                'asset_id' => $this->asset->id,
                'transfer_date' => now(),
                'origin_branch_id' => $this->asset->currentBranch->id,
                'origin_division_id' => $this->asset->currentDivision->id,
                'origin_section_id' => $this->asset->currentSection->id,
                'previous_branch_id' => $this->asset->current_branch_id,
                'previous_division_id' => $this->asset->current_division_id,
                'previous_section_id' => $this->asset->current_section_id,
                'current_branch_id' => $this->target_branch_id,
                'current_division_id' => $this->target_division_id,
                'current_section_id' => $this->target_section_id,
                'remarks' => $this->remarks,
                'transferred_by' => auth()->id()
            ]);

            // Update asset location
            $this->asset->update([
                'current_branch_id' => $this->target_branch_id,
                'current_division_id' => $this->target_division_id,
                'current_section_id' => $this->target_section_id
            ]);

            session()->flash('success', 'Asset transferred successfully.');
            $this->dispatch('asset-transferred');

            return redirect()->route('assets.index');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to transfer asset. Please try again.');
        }
    }

    public function getBranchesProperty()
    {
        $user = auth()->user();
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isStaff())) {
            return Branch::where('is_active', true)->orderBy('name')->get();
        }
        return Branch::where('is_active', true)->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.assets.asset-transfer', [
            'branches' => $this->branches,
        ]);
    }
}