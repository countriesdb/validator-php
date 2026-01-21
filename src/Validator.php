<?php

namespace CountriesDB\Validator;

use CountriesDB\Validator\Exceptions\ValidationException;
use Illuminate\Support\Facades\Http;

/**
 * Standalone validator for CountriesDB
 */
class Validator
{
    private string $apiKey;
    private string $backendUrl;

    public function __construct(string $apiKey, ?string $backendUrl = null)
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('API key is required');
        }

        $this->apiKey = $apiKey;
        $this->backendUrl = $backendUrl ?? 'https://api.countriesdb.com';
    }

    /**
     * Validate a single country code
     *
     * @param string $code ISO 3166-1 alpha-2 country code
     * @param bool $followUpward Whether to check if country is referenced in a subdivision
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateCountry(string $code, bool $followUpward = false): array
    {
        if (empty($code) || strlen($code) !== 2) {
            return [
                'valid' => false,
                'message' => 'Invalid country code.',
            ];
        }

        try {
            $response = $this->makeRequest('/api/validate/country', [
                'code' => strtoupper($code),
                'follow_upward' => $followUpward,
            ]);

            return $response;
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate multiple country codes
     *
     * @param array $codes Array of ISO 3166-1 alpha-2 country codes
     * @return array Array of ['code' => string, 'valid' => bool, 'message' => string|null]
     */
    public function validateCountries(array $codes): array
    {
        if (empty($codes)) {
            return [];
        }

        try {
            $response = $this->makeRequest('/api/validate/country', [
                'code' => array_map('strtoupper', $codes),
            ]);

            return $response['results'] ?? [];
        } catch (\Exception $e) {
            throw new ValidationException('Failed to validate countries: ' . $e->getMessage());
        }
    }

    /**
     * Validate a single subdivision code
     *
     * @param string|null $code Subdivision code (e.g., 'US-CA') or null/empty string
     * @param string $country ISO 3166-1 alpha-2 country code
     * @param bool $followRelated Whether to check if subdivision redirects to another country
     * @param bool $allowParentSelection Whether to allow selecting parent subdivisions
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateSubdivision(?string $code, string $country, bool $followRelated = false, bool $allowParentSelection = false): array
    {
        if (empty($country) || strlen($country) !== 2) {
            return [
                'valid' => false,
                'message' => 'Invalid country code.',
            ];
        }

        try {
            $response = $this->makeRequest('/api/validate/subdivision', [
                'code' => $code ?? '',
                'country' => strtoupper($country),
                'follow_related' => $followRelated,
                'allow_parent_selection' => $allowParentSelection,
            ]);

            return $response;
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate multiple subdivision codes
     *
     * @param array $codes Array of subdivision codes or null/empty strings
     * @param string $country ISO 3166-1 alpha-2 country code
     * @param bool $allowParentSelection Whether to allow selecting parent subdivisions
     * @return array Array of ['code' => string, 'valid' => bool, 'message' => string|null]
     */
    public function validateSubdivisions(array $codes, string $country, bool $allowParentSelection = false): array
    {
        if (empty($codes)) {
            return [];
        }

        try {
            $response = $this->makeRequest('/api/validate/subdivision', [
                'code' => array_map(fn($c) => $c ?? '', $codes),
                'country' => strtoupper($country),
                'allow_parent_selection' => $allowParentSelection,
            ]);

            return $response['results'] ?? [];
        } catch (\Exception $e) {
            throw new ValidationException('Failed to validate subdivisions: ' . $e->getMessage());
        }
    }

    /**
     * Make HTTP request to the API
     *
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array Response data
     * @throws \Exception
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $url = rtrim($this->backendUrl, '/') . $endpoint;

        $response = Http::withToken($this->apiKey)
            ->post($url, $data);

        if ($response->failed()) {
            $errorData = $response->json();
            throw new \Exception($errorData['message'] ?? "HTTP Error: {$response->status()}");
        }

        return $response->json();
    }
}









