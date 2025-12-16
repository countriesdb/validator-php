<?php

namespace CountriesDB\Validator\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use CountriesDB\Validator\Validator;

/**
 * Laravel validation rule for country codes
 */
class ValidCountry implements ValidationRule
{
    private ?Validator $validator = null;
    private bool $followUpward;
    private ?string $errorMessage = null;

    public function __construct(bool $followUpward = false)
    {
        $this->followUpward = $followUpward;
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

        $result = $this->validator->validateCountry($value, $this->followUpward);
        
        if (!$result['valid']) {
            $message = $result['message'] ?? 'The :attribute must be a valid country code.';
            $fail($message);
        }
    }
}









