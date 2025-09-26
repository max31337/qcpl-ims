<?php

namespace App\Livewire;

use App\Models\InvitationRequest;
use Livewire\Component;

class RequestAccessForm extends Component
{
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $department = '';
    public $reason = '';
    public $submitted = false;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:invitation_requests,email',
        'department' => 'nullable|string|max:255',
        'reason' => 'required|string|min:20|max:1000',
    ];

    protected $messages = [
        'email.unique' => 'A request has already been submitted for this email address.',
        'reason.min' => 'Please provide at least 20 characters explaining why you need access.',
    ];

    public function submit()
    {
        $this->validate();

        // Check if email is a government email
        if (!InvitationRequest::isGovernmentEmail($this->email)) {
            $this->addError('email', 'Only government email addresses (ending in .gov.ph or similar) are allowed.');
            return;
        }

        try {
            InvitationRequest::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'department' => $this->department,
                'reason' => $this->reason,
            ]);

            $this->submitted = true;
            $this->reset(['first_name', 'last_name', 'email', 'department', 'reason']);
            
            session()->flash('success', 'Your access request has been submitted successfully! An administrator will review your request.');
        } catch (\Exception $e) {
            session()->flash('error', 'There was an error submitting your request. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.request-access-form');
    }
}
