<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Determine if the input is an email or username
        $loginField = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $credentials = [
            $loginField => $this->email,
            'password' => $this->password,
        ];

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                // Show invalid credentials beneath the password field
                'form.password' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Prevent duplicate red throttle message; client shows a live countdown instead.
        // We still throw to stop processing, but attach to a non-rendered key.
        throw ValidationException::withMessages([
            'throttle' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        // IP-scoped lockout to prevent bypassing by changing/clearing the email field or refreshing.
        return 'login:'.request()->ip();
    }

    /**
     * Public helpers for UI to show live countdown.
     */
    public function isThrottled(): bool
    {
        return RateLimiter::tooManyAttempts($this->throttleKey(), 5);
    }

    public function throttleRemaining(): int
    {
        return RateLimiter::availableIn($this->throttleKey());
    }
}
