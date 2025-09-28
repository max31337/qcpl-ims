<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use App\Models\MfaCode;
use App\Mail\MfaCodeMail;

#[Layout('layouts.app')]
class Profile extends Component
{
    public $name = '';
    public $firstname = '';
    public $middlename = '';
    public $lastname = '';
    public $username = '';
    public $email = '';
    public $employee_id = '';
    
    // Password fields
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';
    
    // MFA fields
    public $mfa_enabled = false;
    public $mfa_code = '';
    public $mfa_verification_step = false;
    public $pending_password_change = false;
    
    // Branch/Division/Section info (read-only for most users)
    public $branch_name = '';
    public $division_name = '';
    public $section_name = '';
    public $role = '';

    public function mount()
    {
        $user = auth()->user();
        
        $this->name = $user->name ?? '';
        $this->firstname = $user->firstname ?? '';
        $this->middlename = $user->middlename ?? '';
        $this->lastname = $user->lastname ?? '';
        $this->username = $user->username ?? '';
        $this->email = $user->email ?? '';
        $this->employee_id = $user->employee_id ?? '';
        $this->role = $user->role ?? '';
        $this->mfa_enabled = $user->mfa_enabled ?? false;
        
        // Load branch/division/section names
        $this->branch_name = $user->branch->name ?? 'Not assigned';
        $this->division_name = $user->division->name ?? 'Not assigned';
        $this->section_name = $user->section->name ?? 'Not assigned';
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username,' . auth()->id()],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
            'employee_id' => ['nullable', 'string', 'max:255'],
        ]);

        auth()->user()->update([
            'name' => $this->name,
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'lastname' => $this->lastname,
            'username' => $this->username,
            'email' => $this->email,
            'employee_id' => $this->employee_id,
        ]);

        session()->flash('success', 'Profile updated successfully.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = auth()->user();

        // If MFA is enabled, require verification
        if ($user->mfa_enabled && !$this->mfa_verification_step) {
            $mfaCode = $user->generateMfaCode('password_change');
            Mail::to($user->email)->send(new MfaCodeMail($user, $mfaCode, 'password_change'));
            
            $this->pending_password_change = true;
            $this->mfa_verification_step = true;
            
            session()->flash('mfa_sent', 'A verification code has been sent to your email.');
            return;
        }

        // Verify MFA code if required
        if ($user->mfa_enabled && $this->mfa_verification_step) {
            $this->validate([
                'mfa_code' => ['required', 'string', 'size:6'],
            ]);

            if (!$user->verifyMfaCode($this->mfa_code, 'password_change')) {
                $this->addError('mfa_code', 'Invalid or expired verification code.');
                return;
            }
        }

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        // Clear all fields
        $this->resetPasswordFields();

        session()->flash('password_success', 'Password updated successfully.');
    }

    public function toggleMfa()
    {
        $user = auth()->user();
        
        if ($this->mfa_enabled) {
            // Enabling MFA - send verification code
            $mfaCode = $user->generateMfaCode('mfa_setup');
            Mail::to($user->email)->send(new MfaCodeMail($user, $mfaCode, 'mfa_setup'));
            
            $this->mfa_verification_step = true;
            session()->flash('mfa_sent', 'A verification code has been sent to your email to enable MFA.');
        } else {
            // Disabling MFA
            $user->disableMfa();
            $this->mfa_verification_step = false;
            session()->flash('success', 'Multi-Factor Authentication has been disabled.');
        }
    }

    public function verifyMfaSetup()
    {
        $this->validate([
            'mfa_code' => ['required', 'string', 'size:6'],
        ]);

        $user = auth()->user();
        
        if (!$user->verifyMfaCode($this->mfa_code, 'mfa_setup')) {
            $this->addError('mfa_code', 'Invalid or expired verification code.');
            return;
        }

        $user->enableMfa(['email']);
        $this->mfa_enabled = true;
        $this->mfa_verification_step = false;
        $this->mfa_code = '';

        session()->flash('success', 'Multi-Factor Authentication has been enabled successfully.');
    }

    public function cancelMfaVerification()
    {
        $this->mfa_verification_step = false;
        $this->pending_password_change = false;
        $this->mfa_code = '';
        $this->resetPasswordFields();
    }

    private function resetPasswordFields()
    {
        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->mfa_verification_step = false;
        $this->pending_password_change = false;
        $this->mfa_code = '';
    }

    public function render()
    {
        return view('livewire.profile');
    }
}