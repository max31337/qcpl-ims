<?php

namespace App\Livewire\Supplies;

use Livewire\Component;
use App\Models\SupplyRequest;
use Illuminate\Support\Facades\Auth;

class ApproveRequests extends Component
{
    public $requests;

    public function mount()
    {
        // Show all pending requests for approval
        $this->requests = SupplyRequest::where('status', 'pending')->get();
    }

    public function approve($id)
    {
        $request = SupplyRequest::find($id);
        if ($request && $request->status === 'pending') {
            // Inventory deduction logic
            $items = is_array($request->items) ? $request->items : json_decode((string)$request->items, true);
            $errors = [];
            foreach ($items as $item) {
                $supply = \App\Models\Supply::find($item['supply_id']);
                if (!$supply) {
                    $errors[] = "Supply ID {$item['supply_id']} not found.";
                    continue;
                }
                if ($supply->quantity < $item['quantity']) {
                    $errors[] = "Insufficient stock for {$supply->description}. Requested: {$item['quantity']}, Available: {$supply->quantity}";
                }
            }
            if (count($errors) > 0) {
                session()->flash('error', implode(' ', $errors));
                return;
            }
            // Deduct quantities
            foreach ($items as $item) {
                $supply = \App\Models\Supply::find($item['supply_id']);
                $supply->quantity -= $item['quantity'];
                $supply->save();
                // Optionally: create transfer record here
            }
            // Approval logic
            if (Auth::user()->role === 'admin') {
                $request->status = 'admin_approved';
                $request->approved_by = 'admin';
                $request->approved_at = now();
            } elseif (Auth::user()->role === 'supply_officer' && $request->status === 'admin_approved') {
                $request->status = 'supply_officer_approved';
                $request->approved_by = 'supply_officer';
                $request->approved_at = now();
            }
            $request->save();
            session()->flash('success', 'Request approved and supplies transferred.');
            // Notify staff user
            $user = \App\Models\User::find($request->user_id);
            if ($user) {
                $user->notify(new \App\Notifications\SupplyRequestStatusChanged($request));
            }
        }
        $this->requests = SupplyRequest::where('status', 'pending')->get();
    }

    public function reject($id)
    {
        $request = SupplyRequest::find($id);
        if ($request && $request->status === 'pending') {
            $request->status = 'rejected';
            $request->rejected_by = Auth::user()->role;
            $request->rejected_at = now();
            $request->save();
            session()->flash('error', 'Request rejected.');
            // Notify staff user
            $user = \App\Models\User::find($request->user_id);
            if ($user) {
                $user->notify(new \App\Notifications\SupplyRequestStatusChanged($request));
            }
        }
    $this->requests = SupplyRequest::where('status', 'pending')->get();
    }

    public function render()
    {
        return view('livewire.supplies.supply-requests', [
            'requests' => $this->requests,
        ]);
    }
}
