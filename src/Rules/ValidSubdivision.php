<?php

namespace CountriesDB\Validator\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use CountriesDB\Validator\Validator;

/**
 * Laravel validation rule for subdivision codes
 */
class ValidSubdivision implements ValidationRule
{
    private ?Validator $validator = null;
    private string $countryAttribute;
    private bool $followRelated;
    private bool $allowParentSelection;

    public function __construct(
        string $countryAttribute = 'country',
        bool $followRelated = false,
        bool $allowParentSelection = false
    ) {
        $this->countryAttribute = $countryAttribute;
        $this->followRelated = $followRelated;
        $this->allowParentSelection = $allowParentSelection;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $apiKey = config('services.countriesdb.private_key');
        if (empty($apiKey)) {
            $fail('CountriesDB API key is not configured.');
            return;
        }

        if (!$this->validator) {
            $backendUrl = config('services.countriesdb.api_url', 'https://api.countriesdb.com');
            $this->validator = new Validator($apiKey, $backendUrl);
        }

        // Get country from request data
        $request = request();
        $country = $request->input($this->countryAttribute);

        if (empty($country)) {
            $fail('The related country field is required before validating subdivisions.');
            return;
        }

        $result = $this->validator->validateSubdivision(
            $value,
            $country,
            $this->followRelated,
            $this->allowParentSelection
        );

        if (!$result['valid']) {
            $message = $result['message'] ?? 'The :attribute must be a valid subdivision code for the selected country.';
            $fail($message);
        }
    }
}









