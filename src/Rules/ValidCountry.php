<?php

namespace CountriesDB\Validator\Rules;

use Illuminate\Contracts\Validation\Rule;
use CountriesDB\Validator\Validator;

/**
 * Laravel validation rule for country codes
 */
class ValidCountry implements Rule
{
    private ?Validator $validator = null;
    private bool $followUpward;

    public function __construct(bool $followUpward = false)
    {
        $this->followUpward = $followUpward;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $apiKey = config('services.countriesdb.private_key');
        if (empty($apiKey)) {
            return false;
        }

        if (!$this->validator) {
            $backendUrl = config('services.countriesdb.api_url', 'https://api.countriesdb.com');
            $this->validator = new Validator($apiKey, $backendUrl);
        }

        $result = $this->validator->validateCountry($value, $this->followUpward);
        return $result['valid'];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid country code.';
    }
}









