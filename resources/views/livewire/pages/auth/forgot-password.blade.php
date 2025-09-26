<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
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
        <h2 class="text-2xl font-semibold text-gray-900 text-center">Reset Password</h2>
        <p class="mt-2 text-sm text-gray-600 text-center">Enter your email address and we'll send you a password reset link</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-green-700 text-sm font-medium">{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-6">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-sm font-medium text-gray-700" />
            <x-text-input wire:model="email" id="email" 
                class="mt-2 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                type="email" name="email" required autofocus autocomplete="email" 
                placeholder="Enter your email address" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                {{ __('Send Password Reset Link') }}
            </button>
        </div>

        <div class="text-center">
            <span class="text-sm text-gray-600">Remember your password?</span>
            <a href="{{ route('login') }}" wire:navigate class="text-sm text-blue-600 hover:text-blue-500 font-medium ml-1">
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
                    <p><strong>Need help?</strong></p>
                    <p class="mt-1">If you're having trouble resetting your password, please contact your system administrator for assistance.</p>
                </div>
            </div>
        </div>
    </div>
</div>
