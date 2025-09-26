<?php

use App\Models\User;
use App\Models\UserInvitation;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $token = '';
    public UserInvitation $invitation;
    
    // User fields
    public string $firstname = '';
    public string $middlename = '';
    public string $lastname = '';
    public string $username = '';
    public string $email = '';
    public string $employee_id = '';
    public string $password = '';
    public string $password_confirmation = '';
    
    // Organization fields
    public $branch_id = '';
    public $division_id = '';
    public $section_id = '';
    public $role = '';
    
    // Data collections
    public $branches = [];
    public $divisions = [];
    public $sections = [];
    public $roles = [
        'staff' => 'Staff',
        'supply_officer' => 'Supply Officer',
        'property_officer' => 'Property Officer',
        'observer' => 'Observer'
    ];

    public function mount($token)
    {
        $this->token = $token;
        $this->invitation = UserInvitation::where('token', $token)->firstOrFail();
        
        if (!$this->invitation->isValid()) {
            abort(404, 'Invalid or expired invitation');
        }
        
        $this->email = $this->invitation->email;
        $this->branches = Branch::all();
    }

    public function updatedBranchId()
    {
        $this->divisions = Division::where('branch_id', $this->branch_id)->get();
        $this->division_id = '';
        $this->section_id = '';
        $this->sections = [];
    }

    public function updatedDivisionId()
    {
        $this->sections = Section::where('division_id', $this->division_id)->get();
        $this->section_id = '';
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        // Double-check invitation is still valid
        if (!$this->invitation->isValid()) {
            session()->flash('error', 'Your invitation has expired. Please request a new one.');
            return;
        }

        $validated = $this->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'employee_id' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'branch_id' => ['required', 'exists:branches,id'],
            'division_id' => ['required', 'exists:divisions,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'role' => ['required', 'in:staff,supply_officer,property_officer,observer'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($validated) {
            // Create the user with all information
            $user = User::create([
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'middlename' => $validated['middlename'],
                'name' => trim($validated['firstname'] . ' ' . ($validated['middlename'] ? $validated['middlename'] . ' ' : '') . $validated['lastname']),
                'username' => $validated['username'],
                'employee_id' => $validated['employee_id'],
                'email' => $this->email,
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'branch_id' => $validated['branch_id'],
                'division_id' => $validated['division_id'],
                'section_id' => $validated['section_id'],
                'approval_status' => 'pending',
                'is_active' => false,
                'email_verified_at' => now(), // Email is pre-verified through invitation
            ]);
            
            // Mark invitation as registered and link to user
            $this->invitation->markAsRegistered($user);

            event(new Registered($user));
        });

        $this->redirect(route('registration.pending'), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-900">Complete Registration</h2>
        <p class="mt-2 text-sm text-gray-600">You've been invited to join QCPL-IMS. Please complete your profile.</p>
        <div class="mt-3 text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
            <strong>Email:</strong> {{ $email }}
        </div>
    </div>

    <form wire:submit="register" class="space-y-6">
        <!-- Personal Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Personal Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div>
                    <x-input-label for="firstname" :value="__('First Name')" class="text-sm font-medium text-gray-700" />
                    <x-text-input wire:model="firstname" id="firstname" 
                        class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        type="text" required autofocus placeholder="Enter your first name" />
                    <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
                </div>

                <!-- Last Name -->
                <div>
                    <x-input-label for="lastname" :value="__('Last Name')" class="text-sm font-medium text-gray-700" />
                    <x-text-input wire:model="lastname" id="lastname" 
                        class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        type="text" required placeholder="Enter your last name" />
                    <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
                </div>
            </div>

            <!-- Middle Name -->
            <div>
                <x-input-label for="middlename" :value="__('Middle Name (Optional)')" class="text-sm font-medium text-gray-700" />
                <x-text-input wire:model="middlename" id="middlename" 
                    class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    type="text" placeholder="Enter your middle name (optional)" />
                <x-input-error :messages="$errors->get('middlename')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Username -->
                <div>
                    <x-input-label for="username" :value="__('Username')" class="text-sm font-medium text-gray-700" />
                    <x-text-input wire:model="username" id="username" 
                        class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        type="text" required placeholder="Choose a username" />
                    <x-input-error :messages="$errors->get('username')" class="mt-2" />
                </div>

                <!-- Employee ID -->
                <div>
                    <x-input-label for="employee_id" :value="__('Employee ID')" class="text-sm font-medium text-gray-700" />
                    <x-text-input wire:model="employee_id" id="employee_id" 
                        class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        type="text" required placeholder="Enter your employee ID" />
                    <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Organization Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Organization Details</h3>
            
            <!-- Branch -->
            <div>
                <x-input-label for="branch_id" :value="__('Branch')" class="text-sm font-medium text-gray-700" />
                <select wire:model.live="branch_id" id="branch_id" 
                    class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    required>
                    <option value="">Select a branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
            </div>

            <!-- Division -->
            <div>
                <x-input-label for="division_id" :value="__('Division')" class="text-sm font-medium text-gray-700" />
                <select wire:model.live="division_id" id="division_id" 
                    class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    required>
                    <option value="">Select a division</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('division_id')" class="mt-2" />
            </div>

            <!-- Section -->
            <div>
                <x-input-label for="section_id" :value="__('Section')" class="text-sm font-medium text-gray-700" />
                <select wire:model="section_id" id="section_id" 
                    class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    required>
                    <option value="">Select a section</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('section_id')" class="mt-2" />
            </div>

            <!-- Role -->
            <div>
                <x-input-label for="role" :value="__('Role')" class="text-sm font-medium text-gray-700" />
                <select wire:model="role" id="role" 
                    class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    required>
                    <option value="">Select your role</option>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>
        </div>

        <!-- Password Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Security</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-gray-700" />
                    <x-text-input wire:model="password" id="password" 
                        class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        type="password" required autocomplete="new-password" 
                        placeholder="Create a password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-sm font-medium text-gray-700" />
                    <x-text-input wire:model="password_confirmation" id="password_confirmation" 
                        class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        type="password" required autocomplete="new-password" 
                        placeholder="Confirm your password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-700">
                    <p><strong>Note:</strong> Your account will be reviewed by an administrator before activation. You will receive an email notification once approved.</p>
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                {{ __('Complete Registration') }}
            </button>
        </div>
    </form>
</div>
