<?php

namespace CountriesDB\Validator\Rules;

use Illuminate\Contracts\Validation\Rule;
use CountriesDB\Validator\Validator;

/**
 * Laravel validation rule for subdivision codes
 */
class ValidSubdivision implements Rule
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

        // Get country from request data
        $request = request();
        $country = $request->input($this->countryAttribute);

        if (empty($country)) {
            return false;
        }

        $result = $this->validator->validateSubdivision(
            $value,
            $country,
            $this->followRelated,
            $this->allowParentSelection
        );

        return $result['valid'];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid subdivision code for the selected country.';
    }
}









