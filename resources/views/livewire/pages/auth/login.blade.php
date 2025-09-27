<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-ui.card class="p-6">
        <div class="mb-6">
            <x-ui.card-title>Welcome back</x-ui.card-title>
            <x-ui.card-description>Please sign in to your account</x-ui.card-description>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <x-ui.alert class="mb-4" variant="info">
                <x-slot:icon>
                    <x-ui.icon name="info" class="w-4 h-4" />
                </x-slot:icon>
                {{ session('status') }}
            </x-ui.alert>
        @endif

        <form wire:submit="login" class="space-y-5">
            <!-- Email or Username -->
            <div>
                <x-ui.label for="email" required>Email or Username</x-ui.label>
                <x-ui.input wire:model="form.email" id="email" name="email" type="text" required autofocus autocomplete="username" placeholder="Enter your email or username" class="mt-2" />
                <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <x-ui.label for="password" required>Password</x-ui.label>
                <x-ui.input wire:model="form.password" id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="mt-2" />
                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <!-- Remember Me -->
                <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input wire:model="form.remember" id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-input text-primary focus:ring-ring" />
                    <span class="text-sm text-muted-foreground">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-primary hover:underline font-medium" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div>
                <x-ui.button type="submit" class="w-full">
                    <x-ui.icon name="check" class="w-4 h-4 mr-2" />
                    {{ __('Sign in') }}
                </x-ui.button>
            </div>

            <div class="text-center">
                <div class="flex items-center justify-center text-sm text-muted-foreground">
                    <x-ui.icon name="shield-check" class="w-4 h-4 mr-2" />
                    Access is managed by system administrators
                </div>
            </div>
        </form>
    </x-ui.card>
</div>
