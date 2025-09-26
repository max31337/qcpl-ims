<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetTransferHistory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AssetHistory extends Component
{
    use WithPagination;

    public $asset;

    public function mount($assetId)
    {
        $this->asset = Asset::with(['category', 'currentBranch', 'currentDivision', 'currentSection'])
                           ->forUser(auth()->user())
                           ->findOrFail($assetId);
    }

    public function getTransferHistoryProperty()
    {
        return AssetTransferHistory::with([
                'originBranch', 'originDivision', 'originSection',
                'previousBranch', 'previousDivision', 'previousSection',
                'currentBranch', 'currentDivision', 'currentSection',
                'transferredBy'
            ])
            ->where('asset_id', $this->asset->id)
            ->orderBy('transfer_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.assets.asset-history', [
            'transferHistory' => $this->transferHistory,
        ]);
    }
}