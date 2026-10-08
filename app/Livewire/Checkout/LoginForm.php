<?php

namespace App\Livewire\Checkout;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): mixed
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts('checkout-login:'.$throttleKey, 5)) {
            $this->addError('email', 'Too many attempts. Please try again in a minute.');

            return null;
        }

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            RateLimiter::hit('checkout-login:'.$throttleKey);
            $this->addError('email', 'These credentials do not match our records.');

            return null;
        }

        RateLimiter::clear('checkout-login:'.$throttleKey);
        session()->regenerate();

        return $this->redirect(route('checkout'), navigate: true);
    }

    public function render()
    {
        return view('livewire.checkout.login-form');
    }
}
