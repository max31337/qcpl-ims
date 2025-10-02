<div class="min-h-screen bg-white flex items-center justify-center p-6">
    <div class="w-full max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-4">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center mb-1 shadow-lg">
                <x-ui.icon name="shield-check" class="h-8 w-8 text-white" />
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Security Verification</h2>
            <p class="text-base text-gray-600">Enter the verification code sent to your email</p>
        </div>

        <!-- Main Card -->
        <x-ui.card class="p-8 shadow-lg border-0 bg-white">
            @if (session()->has('info'))
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 flex items-center">
                    <x-ui.icon name="info" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="text-base">{{ session('info') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 flex items-center">
                    <x-ui.icon name="shield-x" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="text-base">{{ session('error') }}</span>
                </div>
            @endif

            @if (session()->has('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 flex items-center">
                    <x-ui.icon name="check" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="text-base">{{ session('success') }}</span>
                </div>
            @endif

            <div class="space-y-6">
                <!-- Email Info -->
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div class="flex items-center justify-center mb-3">
                        <x-ui.icon name="mail" class="h-5 w-5 text-gray-600 mr-2" />
                        <span class="text-base font-medium text-gray-700">Code sent to:</span>
                    </div>
                    <p class="text-base text-gray-900 font-mono break-all">{{ auth()->user()->email }}</p>
                </div>

                <!-- MFA Form -->
                <form wire:submit="verifyCode" class="space-y-6">
                    <div>
                        <label for="mfa_code" class="block text-base font-medium text-gray-700 mb-3 text-center">
                            Enter verification code
                        </label>
                        <x-ui.input 
                            wire:model="mfa_code" 
                            id="mfa_code"
                            type="text" 
                            maxlength="6" 
                            placeholder="••••••"
                            class="text-center text-3xl font-mono tracking-[0.5em] py-5 bg-gray-50 border-2 focus:border-blue-500 focus:bg-white transition-all rounded-lg"
                            autocomplete="one-time-code"
                            autofocus
                        />
                        @error('mfa_code')
                            <p class="mt-3 text-base text-red-600 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 py-4 text-lg font-semibold shadow-lg">
                        <x-ui.icon name="check" class="mr-2 h-5 w-5" />
                        Verify & Continue
                    </x-ui.button>
                </form>

                <!-- Additional Actions -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <button 
                            wire:click="resendCode" 
                            type="button"
                            class="text-blue-600 hover:text-blue-800 font-medium underline decoration-dotted underline-offset-2 text-base"
                        >
                            Resend code
                        </button>
                        <span class="text-gray-500 text-sm">
                            {{ $attempts }}/{{ $max_attempts }} attempts
                        </span>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <button 
                            wire:click="logout" 
                            type="button"
                            class="w-full text-center text-base text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            <x-ui.icon name="log-out" class="mr-2 h-4 w-4 inline" />
                            Sign out
                        </button>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <x-ui.icon name="shield-check" class="h-5 w-5 text-amber-600 mr-3 mt-0.5 flex-shrink-0" />
                        <div class="text-base text-amber-800">
                            <p class="font-medium">Secure verification</p>
                            <p class="mt-1">Code expires in 10 minutes for your security.</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- Footer -->
        <div class="text-center mt-1">
            <p class="text-base text-gray-400">
                QCPL-IMS • Secure Access
            </p>
        </div>
    </div>
</div>
