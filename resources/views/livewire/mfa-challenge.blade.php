<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg">
                <x-ui.icon name="shield-check" class="h-8 w-8 text-white" />
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-1">Security Verification</h2>
            <p class="text-sm text-gray-500">
                Enter the code sent to your email
            </p>
        </div>

        <!-- Main Card -->
        <x-ui.card class="p-6 shadow-lg border-0 bg-white/80 backdrop-blur-sm">
            @if (session()->has('info'))
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 text-sm flex items-center">
                    <x-ui.icon name="info" class="w-4 h-4 mr-2 flex-shrink-0" />
                    {{ session('info') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm flex items-center">
                    <x-ui.icon name="shield-x" class="w-4 h-4 mr-2 flex-shrink-0" />
                    {{ session('error') }}
                </div>
            @endif

            @if (session()->has('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm flex items-center">
                    <x-ui.icon name="check" class="w-4 h-4 mr-2 flex-shrink-0" />
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-4">
                <!-- Email Info -->
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <div class="flex items-center justify-center mb-1">
                        <x-ui.icon name="mail" class="h-4 w-4 text-gray-600 mr-2" />
                        <span class="text-sm font-medium text-gray-700">Code sent to:</span>
                    </div>
                    <p class="text-sm text-gray-900 font-mono">{{ auth()->user()->email }}</p>
                </div>

                <!-- MFA Form -->
                <form wire:submit="verifyCode" class="space-y-4">
                    <div>
                        <label for="mfa_code" class="block text-sm font-medium text-gray-700 mb-2 text-center">
                            Enter verification code
                        </label>
                        <x-ui.input 
                            wire:model="mfa_code" 
                            id="mfa_code"
                            type="text" 
                            maxlength="6" 
                            placeholder="••••••"
                            class="text-center text-xl font-mono tracking-[0.5em] py-3 bg-gray-50 border-2 focus:border-blue-500 focus:bg-white transition-all"
                            autocomplete="one-time-code"
                            autofocus
                        />
                        @error('mfa_code')
                            <p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 py-3 font-semibold shadow-lg">
                        <x-ui.icon name="check" class="mr-2 h-4 w-4" />
                        Verify & Continue
                    </x-ui.button>
                </form>

                <!-- Additional Actions -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <button 
                            wire:click="resendCode" 
                            type="button"
                            class="text-blue-600 hover:text-blue-800 font-medium underline decoration-dotted underline-offset-2"
                        >
                            Resend code
                        </button>
                        <span class="text-gray-500 text-xs">
                            {{ $attempts }}/{{ $max_attempts }} attempts
                        </span>
                    </div>

                    <div class="border-t border-gray-100 pt-3">
                        <button 
                            wire:click="logout" 
                            type="button"
                            class="w-full text-center text-sm text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            <x-ui.icon name="log-out" class="mr-1 h-3 w-3 inline" />
                            Sign out
                        </button>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mt-4">
                    <div class="flex items-start">
                        <x-ui.icon name="shield-check" class="h-4 w-4 text-amber-600 mr-2 mt-0.5 flex-shrink-0" />
                        <div class="text-xs text-amber-800">
                            <p class="font-medium">Secure verification</p>
                            <p class="mt-1 leading-relaxed">
                                Code expires in 10 minutes for your security.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-xs text-gray-400">
                QCPL-IMS • Secure Access
            </p>
        </div>
    </div>
</div>
