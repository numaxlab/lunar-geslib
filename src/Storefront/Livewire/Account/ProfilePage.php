<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Account;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Actions\Logout;

class ProfilePage extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $vat_no = null;

    public ?string $company_name = null;

    public string $password = '';

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.account.profile');
    }

    public function mount(): void
    {
        $user = Auth::user();
        $this->first_name = $user->name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->vat_no = $user->latestCustomer()?->vat_no;
        $this->company_name = $user->latestCustomer()?->company_name;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],

            'last_name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(config('auth.providers.users.model'))->ignore($user->id),
            ],

            'vat_no' => ['nullable', 'string', 'max:255'],

            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();

        $user->fill($validated);

        $user->latestCustomer()->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'vat_no' => $validated['vat_no'],
            'company_name' => $validated['company_name'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        DB::commit();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}
