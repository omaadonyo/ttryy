<?php

namespace App\Livewire\Checkout;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class RegisterForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): mixed
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->forceFill(['token_balance' => 20])->save();
        $user->tokenTransactions()->create([
            'type' => 'credit',
            'amount' => 20,
            'balance_after' => 20,
            'description' => 'Welcome bonus',
        ]);

        Auth::login($user);
        session()->regenerate();

        // Full redirect (not navigate) so checkout re-renders from the
        // current URL query, which the page keeps in sync with selections.
        return $this->redirect(request()->header('Referer', route('checkout')));
    }

    public function render()
    {
        return view('livewire.checkout.register-form');
    }
}
