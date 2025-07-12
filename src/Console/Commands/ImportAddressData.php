<?php

namespace NumaxLab\Lunar\Geslib\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Laravel\Prompts\Progress;
use Lunar\Models\Country;

use function Laravel\Prompts\progress;

class ImportAddressData extends Command
{
    protected $signature = 'lunar:geslib:import:address-data';

    protected $description = 'Import address data';

    public function handle(): int
    {
        $this->components->info('Importing Countries and States');

        $existing = Country::pluck('iso3');

        $countries = Http::get(
            'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/refs/heads/master/json/countries.json',
        )->object();

        $newCountries = collect($countries)->filter(function ($country) use ($existing) {
            return !$existing->contains($country->iso3);
        });

        if (!$newCountries->count()) {
            $this->components->info('There are no new countries to import');

            return self::SUCCESS;
        }

        $states = Http::get(
            'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/refs/heads/master/json/states.json',
        )->object();

        $states = collect($states);

        progress(
            'Importing Countries and States',
            $newCountries,
            function ($country, Progress $progress) use ($states) {
                $model = Country::create([
                    'name' => $country->name,
                    'iso3' => $country->iso3,
                    'iso2' => $country->iso2,
                    'phonecode' => $country->phone_code,
                    'capital' => $country->capital,
                    'currency' => $country->currency,
                    'native' => $country->native,
                    'emoji' => $country->emoji,
                    'emoji_u' => $country->emojiU,
                ]);

                $countryStates = $states
                    ->filter(function ($state) use ($country) {
                        return $state->country_id === $country->id;
                    })->filter(function ($state) {
                        if ($state->country_code === 'ES') {
                            return $state->type === 'province';
                        }

                        return true;
                    })
                    ->map(function ($state) {
                        return [
                            'name' => $state->name,
                            'code' => $state->state_code,
                        ];
                    });

                $model->states()->createMany($countryStates->toArray());

                $progress->advance();
            },
        );

        $this->components->info('Countries and States imported successfully');

        return self::SUCCESS;
    }
}