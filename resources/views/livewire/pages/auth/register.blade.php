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
    public int $step = 1;
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

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'firstname' => ['required', 'string', 'max:255'],
                'lastname' => ['required', 'string', 'max:255'],
                'middlename' => ['nullable', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
                'employee_id' => ['required', 'string', 'max:255', 'unique:'.User::class],
            ]);
        } elseif ($this->step === 2) {
            $this->validate([
                'branch_id' => ['required', 'exists:branches,id'],
                'division_id' => ['required', 'exists:divisions,id'],
                'section_id' => ['required', 'exists:sections,id'],
                'role' => ['required', 'in:staff,supply_officer,property_officer,observer'],
            ]);
        }
        $this->step = min(3, $this->step + 1);
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
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
    <x-ui.card class="p-6">
        <div class="mb-6">
            <x-ui.card-title>Complete Registration</x-ui.card-title>
            <x-ui.card-description>You've been invited to join QCPL-IMS. Please complete your profile.</x-ui.card-description>
            <div class="mt-3 text-xs text-muted-foreground bg-accent/40 p-3 rounded-md">
                <strong>Email:</strong> {{ $email }}
            </div>
        </div>

        <!-- Step indicator -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex items-center">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium {{ $step >= 1 ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground' }}">1</span>
                        <span class="ml-2 text-sm {{ $step >= 1 ? 'text-foreground' : 'text-muted-foreground' }}">Personal</span>
                    </div>
                </div>
                <div class="flex-1 h-px mx-3 bg-border"></div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium {{ $step >= 2 ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground' }}">2</span>
                        <span class="ml-2 text-sm {{ $step >= 2 ? 'text-foreground' : 'text-muted-foreground' }}">Organization</span>
                    </div>
                </div>
                <div class="flex-1 h-px mx-3 bg-border"></div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium {{ $step >= 3 ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground' }}">3</span>
                        <span class="ml-2 text-sm {{ $step >= 3 ? 'text-foreground' : 'text-muted-foreground' }}">Security</span>
                    </div>
                </div>
            </div>
        </div>

        <form wire:submit="register" class="space-y-6">
            @if($step === 1)
                <!-- Personal Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-foreground border-b border-border pb-2">Personal Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-ui.label for="firstname" required>First Name</x-ui.label>
                            <x-ui.input wire:model="firstname" id="firstname" type="text" required autofocus placeholder="Enter your first name" class="mt-2" />
                            <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
                        </div>

                        <div>
                            <x-ui.label for="lastname" required>Last Name</x-ui.label>
                            <x-ui.input wire:model="lastname" id="lastname" type="text" required placeholder="Enter your last name" class="mt-2" />
                            <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-ui.label for="middlename">Middle Name (Optional)</x-ui.label>
                        <x-ui.input wire:model="middlename" id="middlename" type="text" placeholder="Enter your middle name (optional)" class="mt-2" />
                        <x-input-error :messages="$errors->get('middlename')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-ui.label for="username" required>Username</x-ui.label>
                            <x-ui.input wire:model="username" id="username" type="text" required placeholder="Choose a username" class="mt-2" />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <div>
                            <x-ui.label for="employee_id" required>Employee ID</x-ui.label>
                            <x-ui.input wire:model="employee_id" id="employee_id" type="text" required placeholder="Enter your employee ID" class="mt-2" />
                            <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
                        </div>
                    </div>
                </div>
            @endif

            @if($step === 2)
                <!-- Organization Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-foreground border-b border-border pb-2">Organization Details</h3>

                    <div>
                        <x-ui.label for="branch_id" required>Branch</x-ui.label>
                        <x-ui.select wire:model.live="branch_id" id="branch_id" required class="mt-2">
                            <option value="">Select a branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-ui.label for="division_id" required>Division</x-ui.label>
                        <x-ui.select wire:model.live="division_id" id="division_id" required class="mt-2">
                            <option value="">Select a division</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('division_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-ui.label for="section_id" required>Section</x-ui.label>
                        <x-ui.select wire:model="section_id" id="section_id" required class="mt-2">
                            <option value="">Select a section</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('section_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-ui.label for="role" required>Role</x-ui.label>
                        <x-ui.select wire:model="role" id="role" required class="mt-2">
                            <option value="">Select your role</option>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>
                </div>
            @endif

            @if($step === 3)
                <!-- Password Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-foreground border-b border-border pb-2">Security</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-ui.label for="password" required>Password</x-ui.label>
                            <x-ui.input wire:model="password" id="password" type="password" required autocomplete="new-password" placeholder="Create a password" class="mt-2" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-ui.label for="password_confirmation" required>Confirm Password</x-ui.label>
                            <x-ui.input wire:model="password_confirmation" id="password_confirmation" type="password" required autocomplete="new-password" placeholder="Confirm your password" class="mt-2" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>
                </div>
            @endif

            <x-ui.alert variant="info">
                <x-slot:icon>
                    <x-ui.icon name="info" class="w-4 h-4" />
                </x-slot:icon>
                <p class="text-sm"><strong>Note:</strong> Your account will be reviewed by an administrator before activation. You will receive an email notification once approved.</p>
            </x-ui.alert>

            <div class="flex items-center justify-between gap-3">
                <div>
                    @if($step > 1)
                        <x-ui.button type="button" variant="outline" wire:click="prevStep">
                            <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                            Back
                        </x-ui.button>
                    @endif
                </div>
                <div class="ml-auto">
                    @if($step < 3)
                        <x-ui.button type="button" wire:click="nextStep">
                            Next
                            <x-ui.icon name="chevron-right" class="w-4 h-4 ml-2" />
                        </x-ui.button>
                    @else
                        <x-ui.button type="submit">
                            <x-ui.icon name="check" class="w-4 h-4 mr-2" />
                            {{ __('Complete Registration') }}
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </form>
    </x-ui.card>
</div>
