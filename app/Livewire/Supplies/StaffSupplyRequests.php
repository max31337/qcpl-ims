<?php

namespace App\Livewire\Supplies;

use Livewire\Component;
use App\Models\SupplyRequest;
use Illuminate\Support\Facades\Auth;

class StaffSupplyRequests extends Component
{
    public $requests;

    public function mount()
    {
        $this->requests = SupplyRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

    // Preload all supplies for mapping (use 'description' field)
    $this->supplyNames = \App\Models\Supply::pluck('description', 'id');
    }

    public function render()
    {
        return view('livewire.supplies.my-requests', [
            'requests' => $this->requests,
            'supplyNames' => $this->supplyNames,
        ]);
    }
}
