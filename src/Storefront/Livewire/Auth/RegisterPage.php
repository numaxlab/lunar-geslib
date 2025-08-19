<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Customer;

class RegisterPage extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $privacy_policy = '';

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.auth.register')
            ->title(__('Regístrate'));
    }

    public function register(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.config('auth.providers.users.model'),
            ],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'privacy_policy' => ['accepted', 'required'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        DB::beginTransaction();

        $user = config('auth.providers.users.model')::create($validated);

        $customer = Customer::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
        ]);

        $customer->users()->attach($user);
        $customer->customerGroups()->attach(StorefrontSession::getCustomerGroups()->first());

        DB::commit();

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}
