<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    //
}; ?>

<div>
    <div class="mb-8">
        <div class="flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mx-auto mb-6">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h2 class="text-2xl font-semibold text-gray-900 text-center">Registration Complete</h2>
        <p class="mt-3 text-center text-gray-600 leading-relaxed">
            Thank you for completing your registration! Your account is now pending administrator approval.
        </p>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h3 class="text-sm font-medium text-yellow-800 mb-2">What happens next?</h3>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• A system administrator will review your registration</li>
                    <li>• You will receive an email notification once your account is approved</li>
                    <li>• If additional information is needed, an administrator will contact you</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" wire:navigate 
            class="text-sm text-blue-600 hover:text-blue-500 font-medium transition-colors duration-200">
            ← Back to sign in
        </a>
    </div>
</div>