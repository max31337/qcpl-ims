<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;

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

        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);

        // Clear password fields
        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';

        session()->flash('password_success', 'Password updated successfully.');
    }

    public function render()
    {
        return view('livewire.profile');
    }
}