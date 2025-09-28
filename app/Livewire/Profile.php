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
    
    // Password strength properties
    public $passwordStrength = 0;
    public $passwordChecks = [
        'length' => false,
        'uppercase' => false,
        'lowercase' => false,
        'numbers' => false,
        'special' => false,
    ];
    
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

    public function updatedPassword()
    {
        $this->checkPasswordStrength();
    }

    private function checkPasswordStrength()
    {
        $password = $this->password;
        $this->passwordStrength = 0;
        
        // Reset checks
        $this->passwordChecks = [
            'length' => false,
            'uppercase' => false,
            'lowercase' => false,
            'numbers' => false,
            'special' => false,
        ];
        
        if (empty($password)) {
            return;
        }
        
        // Check length (at least 8 characters)
        if (strlen($password) >= 8) {
            $this->passwordChecks['length'] = true;
            $this->passwordStrength++;
        }
        
        // Check uppercase letters
        if (preg_match('/[A-Z]/', $password)) {
            $this->passwordChecks['uppercase'] = true;
            $this->passwordStrength++;
        }
        
        // Check lowercase letters
        if (preg_match('/[a-z]/', $password)) {
            $this->passwordChecks['lowercase'] = true;
            $this->passwordStrength++;
        }
        
        // Check numbers
        if (preg_match('/\d/', $password)) {
            $this->passwordChecks['numbers'] = true;
            $this->passwordStrength++;
        }
        
        // Check special characters
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $this->passwordChecks['special'] = true;
            $this->passwordStrength++;
        }
    }

    public function getPasswordStrengthTextProperty()
    {
        return match($this->passwordStrength) {
            0, 1 => 'Very Weak',
            2 => 'Weak',
            3 => 'Fair',
            4 => 'Good',
            5 => 'Strong',
            default => 'Very Weak'
        };
    }

    public function getPasswordStrengthColorProperty()
    {
        return match($this->passwordStrength) {
            0, 1 => 'text-red-600',
            2 => 'text-orange-600',
            3 => 'text-yellow-600',
            4 => 'text-blue-600',
            5 => 'text-green-600',
            default => 'text-red-600'
        };
    }

    public function getPasswordStrengthBgProperty()
    {
        return match($this->passwordStrength) {
            0, 1 => 'bg-red-500',
            2 => 'bg-orange-500',
            3 => 'bg-yellow-500',
            4 => 'bg-blue-500',
            5 => 'bg-green-500',
            default => 'bg-red-500'
        };
    }

    public function getPasswordStrengthPercentageProperty()
    {
        return ($this->passwordStrength / 5) * 100;
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
        
        // Reset password strength
        $this->passwordStrength = 0;
        $this->passwordChecks = [
            'length' => false,
            'uppercase' => false,
            'lowercase' => false,
            'numbers' => false,
            'special' => false,
        ];
    }

    public function render()
    {
        return view('livewire.profile');
    }
}