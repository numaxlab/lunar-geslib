<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Account\Forms;

use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Lunar\Models\Address;
use Lunar\Models\Country;
use Lunar\Models\State;

class AddressForm extends Form
{
    public ?Address $address = null;

    public Collection $countries;

    public Collection $states;

    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $company_name = null;

    #[Validate('required')]
    public ?int $country_id = null;

    #[Validate('required')]
    public ?int $state = null;

    #[Validate('required|string|max:20')]
    public string $postcode = '';

    #[Validate('required|string|max:255')]
    public string $city = '';

    #[Validate('required|string|max:255')]
    public string $line_one = '';

    #[Validate('nullable|string|max:255')]
    public ?string $line_two = null;

    #[Validate('boolean')]
    public bool $shipping_default = false;

    public function loadCountries(): void
    {
        $this->countries = Country::orderBy('name')->get();
        $this->states = collect();
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;

        $this->first_name = $this->address->first_name;
        $this->last_name = $this->address->last_name;
        $this->company_name = $this->address->company_name;
        $this->country_id = $this->address->country_id;
        $this->state = (int) $this->address->state;
        $this->postcode = $this->address->postcode;
        $this->city = $this->address->city;
        $this->line_one = $this->address->line_one;
        $this->line_two = $this->address->line_two;
        $this->shipping_default = $this->address->shipping_default;

        if ($this->country_id !== null) {
            $this->updateStates($this->country_id);
        }
    }

    public function updateStates(?int $countryId = null): void
    {
        $this->states = State::where('country_id', $countryId)
            ->orderBy('name')
            ->get();

        $this->state = null;
    }

    public function store(int $customerId): void
    {
        $validated = $this->validate();

        if ($this->address instanceof \Lunar\Models\Address) {
            $this->address->update($validated);
        } else {
            $validated['customer_id'] = $customerId;

            Address::create($validated);
        }
    }
}
