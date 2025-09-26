<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <div class="mb-8">
        <div class="flex items-center justify-center mb-6">
            <img src="{{ asset('Quezon_City_Public_Library_logo.png') }}" alt="QC Public Library" class="w-16 h-16 object-contain mr-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">QCPL-IMS</h1>
                <p class="text-sm text-gray-500">Inventory Management</p>
            </div>
        </div>
        <h2 class="text-2xl font-semibold text-gray-900 text-center">Create New Password</h2>
        <p class="mt-2 text-sm text-gray-600 text-center">Enter your new password to complete the reset process</p>
    </div>

    <form wire:submit="resetPassword" class="space-y-6">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-sm font-medium text-gray-700" />
            <x-text-input wire:model="email" id="email" 
                class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                type="email" name="email" required autofocus autocomplete="username" 
                placeholder="Your email address" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('New password')" class="text-sm font-medium text-gray-700" />
            <x-text-input wire:model="password" id="password" 
                class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                type="password" name="password" required autocomplete="new-password" 
                placeholder="Enter your new password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" class="text-sm font-medium text-gray-700" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" 
                class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                type="password" name="password_confirmation" required autocomplete="new-password" 
                placeholder="Confirm your new password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                {{ __('Reset Password') }}
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" wire:navigate class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                {{ __('Back to sign in') }}
            </a>
        </div>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-700">
                    <p><strong>Password Requirements:</strong></p>
                    <p class="mt-1">Choose a strong password with at least 8 characters for better security.</p>
                </div>
            </div>
        </div>
    </div>
</div>
