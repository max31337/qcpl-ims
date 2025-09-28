<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Profile</h1>
            <p class="text-muted-foreground">Manage your account settings and personal information</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            <div class="flex">
                <x-ui.icon name="check" class="h-4 w-4 text-green-400" />
                <div class="ml-3">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if (session()->has('password_success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            <div class="flex">
                <x-ui.icon name="check" class="h-4 w-4 text-green-400" />
                <div class="ml-3">{{ session('password_success') }}</div>
            </div>
        </div>
    @endif

    @if (session()->has('mfa_sent'))
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
            <div class="flex">
                <x-ui.icon name="info" class="h-4 w-4 text-blue-400" />
                <div class="ml-3">{{ session('mfa_sent') }}</div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Information -->
        <div class="lg:col-span-2">
            <x-ui.card class="p-6">
                <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
                <p class="text-sm text-muted-foreground mb-6">Update your account's profile information and email address.</p>

                <form wire:submit="updateProfile" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Display Name <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input wire:model="name" required class="mt-1.5" />
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Employee ID
                            </label>
                            <x-ui.input wire:model="employee_id" class="mt-1.5" />
                            @error('employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input wire:model="firstname" required class="mt-1.5" />
                            @error('firstname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Middle Name
                            </label>
                            <x-ui.input wire:model="middlename" class="mt-1.5" />
                            @error('middlename') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input wire:model="lastname" required class="mt-1.5" />
                            @error('lastname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input wire:model="username" required class="mt-1.5" />
                            @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input wire:model="email" type="email" required class="mt-1.5" />
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button type="submit">
                            <x-ui.icon name="check" class="mr-2 h-4 w-4" />
                            Save Changes
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <!-- Password Update -->
            <x-ui.card class="p-6 mt-6">
                <h2 class="text-xl font-semibold mb-4">Update Password</h2>
                <p class="text-sm text-muted-foreground mb-6">Ensure your account is using a long, random password to stay secure.</p>

                @if($mfa_verification_step && ($pending_password_change || auth()->user()->mfa_enabled))
                    <!-- MFA Verification Step -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-medium text-blue-900 mb-2">Email Verification Required</h3>
                        <p class="text-sm text-blue-700 mb-4">Please enter the 6-digit code sent to your email address.</p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-blue-900">Verification Code</label>
                                <x-ui.input wire:model="mfa_code" type="text" placeholder="000000" maxlength="6" class="mt-1.5 font-mono text-center text-lg tracking-widest" />
                                @error('mfa_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="flex gap-2">
                                <x-ui.button wire:click="verifyMfaSetup" type="button">
                                    <x-ui.icon name="check" class="mr-2 h-4 w-4" />
                                    Verify Code
                                </x-ui.button>
                                <x-ui.button wire:click="cancelMfaVerification" variant="outline" type="button">
                                    Cancel
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Password Form -->
                    <form wire:submit="updatePassword" class="space-y-4">
                        @if(!$pending_password_change)
                            <div>
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Current Password <span class="text-red-500">*</span>
                                </label>
                                <x-ui.input wire:model="current_password" type="password" required class="mt-1.5" />
                                @error('current_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                        New Password <span class="text-red-500">*</span>
                                    </label>
                                    <x-ui.input wire:model.live="password" type="password" required class="mt-1.5" x-data="passwordStrength" @input="checkStrength($event.target.value)" />
                                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    
                                    <!-- Password Strength Indicator -->
                                    <div class="mt-2" x-show="password.length > 0" x-transition>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-xs font-medium">Password Strength:</span>
                                            <span class="text-xs font-semibold" :class="strengthColor" x-text="strengthText"></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full transition-all duration-300" :class="strengthBg" :style="`width: ${strengthPercentage}%`"></div>
                                        </div>
                                        <div class="mt-2 space-y-1">
                                            <div class="flex items-center gap-2 text-xs">
                                                <span :class="checks.length ? 'text-green-600' : 'text-gray-400'">✓</span>
                                                <span>At least 8 characters</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <span :class="checks.uppercase ? 'text-green-600' : 'text-gray-400'">✓</span>
                                                <span>Contains uppercase letter</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <span :class="checks.lowercase ? 'text-green-600' : 'text-gray-400'">✓</span>
                                                <span>Contains lowercase letter</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <span :class="checks.numbers ? 'text-green-600' : 'text-gray-400'">✓</span>
                                                <span>Contains number</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <span :class="checks.special ? 'text-green-600' : 'text-gray-400'">✓</span>
                                                <span>Contains special character</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <x-ui.input wire:model="password_confirmation" type="password" required class="mt-1.5" />
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <x-ui.button type="submit">
                                    <x-ui.icon name="check" class="mr-2 h-4 w-4" />
                                    Update Password
                                </x-ui.button>
                            </div>
                        @endif
                    </form>
                @endif
            </x-ui.card>

            <!-- Multi-Factor Authentication -->
            <x-ui.card class="p-6 mt-6">
                <h2 class="text-xl font-semibold mb-4">Multi-Factor Authentication</h2>
                <p class="text-sm text-muted-foreground mb-6">Add an extra layer of security to your account by enabling email-based verification.</p>

                <div class="flex items-center justify-between p-4 border border-border rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <x-ui.icon name="shield-check" class="w-5 h-5 text-blue-600" />
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">Email Authentication</div>
                            <div class="text-sm text-muted-foreground">Receive verification codes via email</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($mfa_enabled)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Enabled
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Disabled
                            </span>
                        @endif
                        <button wire:click="toggleMfa" 
                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                            {{ $mfa_enabled ? 'Disable' : 'Enable' }}
                        </button>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Account Information (Read-only) -->
        <div class="lg:col-span-1">
            <x-ui.card class="p-6">
                <h2 class="text-xl font-semibold mb-4">Account Information</h2>
                <p class="text-sm text-muted-foreground mb-6">Your account details and organizational information.</p>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Role</label>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                {{ ucfirst(str_replace('_', ' ', $role)) }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Branch</label>
                        <p class="text-sm text-foreground mt-1">{{ $branch_name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Division</label>
                        <p class="text-sm text-foreground mt-1">{{ $division_name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Section</label>
                        <p class="text-sm text-foreground mt-1">{{ $section_name }}</p>
                    </div>

                    <div class="pt-4 border-t border-border">
                        <p class="text-xs text-muted-foreground">
                            <x-ui.icon name="info" class="inline mr-1 h-3 w-3" />
                            Contact your administrator to update organizational assignments or role permissions.
                        </p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('passwordStrength', () => ({
        password: '',
        strength: 0,
        checks: {
            length: false,
            uppercase: false,
            lowercase: false,
            numbers: false,
            special: false
        },
        
        get strengthPercentage() {
            return (this.strength / 5) * 100;
        },
        
        get strengthText() {
            if (this.strength <= 1) return 'Very Weak';
            if (this.strength <= 2) return 'Weak';
            if (this.strength <= 3) return 'Fair';
            if (this.strength <= 4) return 'Good';
            return 'Strong';
        },
        
        get strengthColor() {
            if (this.strength <= 1) return 'text-red-600';
            if (this.strength <= 2) return 'text-orange-600';
            if (this.strength <= 3) return 'text-yellow-600';
            if (this.strength <= 4) return 'text-blue-600';
            return 'text-green-600';
        },
        
        get strengthBg() {
            if (this.strength <= 1) return 'bg-red-500';
            if (this.strength <= 2) return 'bg-orange-500';
            if (this.strength <= 3) return 'bg-yellow-500';
            if (this.strength <= 4) return 'bg-blue-500';
            return 'bg-green-500';
        },
        
        checkStrength(password) {
            this.password = password;
            this.strength = 0;
            
            // Check length
            this.checks.length = password.length >= 8;
            if (this.checks.length) this.strength++;
            
            // Check uppercase
            this.checks.uppercase = /[A-Z]/.test(password);
            if (this.checks.uppercase) this.strength++;
            
            // Check lowercase
            this.checks.lowercase = /[a-z]/.test(password);
            if (this.checks.lowercase) this.strength++;
            
            // Check numbers
            this.checks.numbers = /\d/.test(password);
            if (this.checks.numbers) this.strength++;
            
            // Check special characters
            this.checks.special = /[^A-Za-z0-9]/.test(password);
            if (this.checks.special) this.strength++;
        }
    }));
});
</script>