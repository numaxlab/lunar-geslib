<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Checkout\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class AddressForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $company_name = null;

    #[Validate('required')]
    public ?int $country_id = null;

    #[Validate('nullable')]
    public ?string $state = null;

    #[Validate('required|string|max:20')]
    public string $postcode = '';

    #[Validate('required|string|max:255')]
    public string $city = '';

    #[Validate('required|string|max:255')]
    public string $line_one = '';

    #[Validate('nullable|string|max:255')]
    public ?string $line_two = null;

    #[Validate('nullable|string|max:255')]
    public ?string $delivery_instructions = null;

    #[Validate('required|email|max:255')]
    public string $contact_email = '';

    #[Validate('nullable|string|max:20')]
    public ?string $contact_phone = null;
}
