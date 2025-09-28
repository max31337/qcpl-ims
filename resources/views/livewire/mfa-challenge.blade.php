<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-blue-600 rounded-full flex items-center justify-center mb-6">
                <x-ui.icon name="shield-check" class="h-10 w-10 text-white" />
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Multi-Factor Authentication</h2>
            <p class="mt-2 text-sm text-gray-600">
                Security verification required to access your account
            </p>
        </div>

        <!-- Main Card -->
        <x-ui.card class="p-8 shadow-2xl">
            @if (session()->has('info'))
                <x-ui.alert class="mb-6" variant="info">
                    <x-slot:icon>
                        <x-ui.icon name="info" class="w-4 h-4" />
                    </x-slot:icon>
                    {{ session('info') }}
                </x-ui.alert>
            @endif

            @if (session()->has('error'))
                <x-ui.alert class="mb-6" variant="destructive">
                    <x-slot:icon>
                        <x-ui.icon name="shield-x" class="w-4 h-4" />
                    </x-slot:icon>
                    {{ session('error') }}
                </x-ui.alert>
            @endif

            @if (session()->has('success'))
                <x-ui.alert class="mb-6" variant="success">
                    <x-slot:icon>
                        <x-ui.icon name="check" class="w-4 h-4" />
                    </x-slot:icon>
                    {{ session('success') }}
                </x-ui.alert>
            @endif

            <div class="space-y-6">
                <!-- Email Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-ui.icon name="mail" class="h-5 w-5 text-blue-600 mr-3" />
                        <div>
                            <p class="text-sm font-medium text-blue-900">Verification code sent to:</p>
                            <p class="text-sm text-blue-700">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- MFA Form -->
                <form wire:submit="verifyCode" class="space-y-6">
                    <div>
                        <label for="mfa_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Enter 6-digit verification code
                        </label>
                        <x-ui.input 
                            wire:model="mfa_code" 
                            id="mfa_code"
                            type="text" 
                            maxlength="6" 
                            placeholder="000000"
                            class="text-center text-2xl font-mono tracking-widest"
                            autocomplete="one-time-code"
                            autofocus
                        />
                        @error('mfa_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.button type="submit" class="w-full bg-blue-600 hover:bg-blue-700">
                            <x-ui.icon name="check" class="mr-2 h-4 w-4" />
                            Verify Code
                        </x-ui.button>
                    </div>
                </form>

                <!-- Additional Actions -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between text-sm">
                        <button 
                            wire:click="resendCode" 
                            type="button"
                            class="text-blue-600 hover:text-blue-800 font-medium"
                        >
                            Resend Code
                        </button>
                        <span class="text-gray-500">
                            Attempts: {{ $attempts }}/{{ $max_attempts }}
                        </span>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <button 
                            wire:click="logout" 
                            type="button"
                            class="w-full text-center text-sm text-gray-600 hover:text-gray-800"
                        >
                            <x-ui.icon name="log-out" class="mr-2 h-4 w-4 inline" />
                            Sign out and return to login
                        </button>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <x-ui.icon name="shield-check" class="h-5 w-5 text-yellow-600 mr-3 mt-0.5" />
                        <div class="text-sm">
                            <p class="font-medium text-yellow-800">Security Notice</p>
                            <p class="text-yellow-700 mt-1">
                                This additional security step helps protect your account. 
                                The code expires in 10 minutes.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- Footer -->
        <div class="text-center">
            <p class="text-xs text-gray-500">
                Quezon City Public Library - Inventory Management System
            </p>
        </div>
    </div>
</div>
