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

                <form wire:submit="updatePassword" class="space-y-4">
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
                            <x-ui.input wire:model="password" type="password" required class="mt-1.5" />
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                </form>
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