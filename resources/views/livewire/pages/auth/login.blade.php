<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Validation\ValidationException;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        try {
            $this->form->authenticate();
        } catch (ValidationException $e) {
            // If a throttle is in effect, inform the client with an absolute timestamp
            if ($this->isThrottled()) {
                $remaining = $this->throttleRemaining();
                $this->dispatch('login-throttle', lockUntil: now()->addSeconds($remaining)->timestamp);
            }
            throw $e;
        }

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    // Expose throttle info to the view so Alpine can initialize a countdown.
    public function throttleRemaining(): int
    {
        $key = 'login:'.request()->ip();
        return RateLimiter::availableIn($key);
    }

    public function isThrottled(): bool
    {
        $key = 'login:'.request()->ip();
        return RateLimiter::tooManyAttempts($key, 5);
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
                <!-- Throttle live message placed under password to avoid duplicate red alert -->
                <p id="login-throttle-msg" class="text-xs text-muted-foreground text-left hidden mt-2">
                    Too many attempts. Please wait <span id="login-countdown"></span> seconds before trying again.
                </p>
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

            <div class="space-y-2">
                <x-ui.button id="login-submit" type="submit" class="w-full">
                    <x-ui.icon name="check" class="w-4 h-4 mr-2" />
                    <span id="login-button-label">{{ __('Sign in') }}</span>
                </x-ui.button>
            </div>

            <!-- Initial lock-until timestamp for countdown bootstrap -->
            <span id="login-lock-until" class="hidden" data-lock-until="{{ ($this->throttleRemaining() > 0) ? now()->addSeconds($this->throttleRemaining())->timestamp : 0 }}"></span>

            <div class="text-center">
                <div class="flex items-center justify-center text-sm text-muted-foreground">
                    <x-ui.icon name="shield-check" class="w-4 h-4 mr-2" />
                    Access is managed by system administrators
                </div>
            </div>
        </form>
    </x-ui.card>
</div>

<script>
// Server-driven countdown that survives Livewire updates and dev HMR.
(function() {
    const btn = () => document.getElementById('login-submit');
    const label = () => document.getElementById('login-button-label');
    const msg = () => document.getElementById('login-throttle-msg');
    const out = () => document.getElementById('login-countdown');
    const lockEl = () => document.getElementById('login-lock-until');

    function setHidden(el, hidden) {
        if (!el) return;
        if (hidden) el.classList.add('hidden'); else el.classList.remove('hidden');
    }

    function startCountdown(lockUntilTs) {
        if (!window.__loginCountdown) window.__loginCountdown = {};
        if (window.__loginCountdown.timer) {
            clearInterval(window.__loginCountdown.timer);
            window.__loginCountdown.timer = null;
        }
        window.__loginCountdown.lockUntil = parseInt(lockUntilTs || 0, 10);

        const update = () => {
            const now = Math.floor(Date.now()/1000);
            const remaining = Math.max(0, (window.__loginCountdown.lockUntil || 0) - now);
            if (out()) out().textContent = String(remaining);
            if (btn()) btn().disabled = remaining > 0;
            if (label()) label().textContent = remaining > 0 ? ('Try again in ' + remaining + 's') : 'Sign in';
            setHidden(msg(), !(remaining > 0));
            if (remaining <= 0 && window.__loginCountdown.timer) {
                clearInterval(window.__loginCountdown.timer);
                window.__loginCountdown.timer = null;
            }
        };

        update();
        window.__loginCountdown.timer = setInterval(update, 1000);
    }

    // Listen for server-dispatched throttle event
    window.addEventListener('login-throttle', function(e) {
        const ts = e.detail && e.detail.lockUntil ? parseInt(e.detail.lockUntil, 10) : 0;
        if (ts) startCountdown(ts);
    });

    // Bootstrap on initial load or after Livewire DOM changes
    function bootstrapFromDom() {
        const el = lockEl();
        if (!el) return;
        const ts = parseInt(el.getAttribute('data-lock-until') || '0', 10);
        if (ts && ts > Math.floor(Date.now()/1000)) startCountdown(ts); else {
            if (btn()) btn().disabled = false;
            if (label()) label().textContent = 'Sign in';
            setHidden(msg(), true);
        }
    }

    document.addEventListener('DOMContentLoaded', bootstrapFromDom);
    // Livewire v3 fires this after navigation/updates
    window.addEventListener('livewire:navigated', bootstrapFromDom);
})();
</script>
