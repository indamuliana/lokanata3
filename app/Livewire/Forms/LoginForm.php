<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class LoginForm extends Form
{
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    /**
     * Validation rules for the login form.
     */
    public function rules(): array
    {
        return [
            'username' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials using username or email.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = trim($this->username ?: $this->email);

        if ($loginInput === '') {
            throw ValidationException::withMessages([
                'form.username' => 'Username atau email wajib diisi.',
                'form.email' => 'Username atau email wajib diisi.',
            ]);
        }

        $user = \App\Models\User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if ($user && ! $user->is_active) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.username' => 'Akun Anda berstatus nonaktif. Silakan hubungi Manager untuk aktivasi kembali.',
                'form.email' => 'Akun Anda berstatus nonaktif. Silakan hubungi Manager untuk aktivasi kembali.',
            ]);
        }

        $authenticated = false;

        if ($user) {
            if (!empty($user->username) && Auth::attempt(['username' => $user->username, 'password' => $this->password], $this->remember)) {
                $authenticated = true;
            } elseif (!empty($user->email) && Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
                $authenticated = true;
            }
        } else {
            $authenticated = Auth::attempt(['username' => $loginInput, 'password' => $this->password], $this->remember)
                || Auth::attempt(['email' => $loginInput, 'password' => $this->password], $this->remember);
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.username' => trans('auth.failed'),
                'form.email' => trans('auth.failed'),
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

        throw ValidationException::withMessages([
            'form.username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
            'form.email' => trans('auth.throttle', [
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
        $loginInput = trim($this->username ?: $this->email);
        return Str::transliterate(Str::lower($loginInput).'|'.request()->ip());
    }
}
