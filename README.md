# countriesdb/validator

**Backend validation package for CountriesDB.** Provides server-side validation for country and subdivision codes using ISO 3166-1 and ISO 3166-2 standards.

[![Latest Version](https://img.shields.io/packagist/v/countriesdb/validator.svg?style=flat-square)](https://packagist.org/packages/countriesdb/validator)
[![Total Downloads](https://img.shields.io/packagist/dt/countriesdb/validator.svg?style=flat-square)](https://packagist.org/packages/countriesdb/validator)
[![License](https://img.shields.io/packagist/l/countriesdb/validator.svg?style=flat-square)](https://packagist.org/packages/countriesdb/validator)

📖 **[Full Documentation](https://countriesdb.com/docs/backend-api)** | 🌐 **[Website](https://countriesdb.com)**

**Important**: This package only provides validation methods. Data fetching for frontend widgets must be done through frontend packages ([`@countriesdb/widget-core`](https://www.npmjs.com/package/@countriesdb/widget-core), [`@countriesdb/widget`](https://www.npmjs.com/package/@countriesdb/widget)).

## Getting Started

**⚠️ API Key Required:** This package requires a CountriesDB **private** API key to function. You must create an account at [countriesdb.com](https://countriesdb.com) to obtain your private API key. Test accounts are available with limited functionality.

- 🔑 [Get your API key](https://countriesdb.com) - Create an account and get your private key
- 📚 [View documentation](https://countriesdb.com/docs/backend-api) - Complete API reference and examples
- 💬 [Support](https://countriesdb.com) - Get help and support

## Features

- ✅ **Laravel Validation Rules** - Drop-in validation rules (`ValidCountry`, `ValidSubdivision`) for Laravel forms
- ✅ **Standalone Validator** - Use the `Validator` class directly in any PHP application
- ✅ **ISO 3166 Compliant** - Validates ISO 3166-1 (countries) and ISO 3166-2 (subdivisions) codes
- ✅ **Multiple Validation Options** - Support for `follow_upward`, `follow_related`, and `allow_parent_selection`
- ✅ **Batch Validation** - Validate multiple countries or subdivisions in a single request
- ✅ **Laravel 9-12 Support** - Compatible with Laravel 9.0, 10.0, 11.0, and 12.0
- ✅ **Detailed Error Messages** - Returns specific error messages from the CountriesDB API

## Installation

```bash
composer require countriesdb/validator
```

## Usage

### Standalone Validator

```php
use CountriesDB\Validator\Validator;

$validator = new Validator('YOUR_API_KEY');

// Validate a single country
$result = $validator->validateCountry('US');
if ($result['valid']) {
    echo "Valid country";
} else {
    echo "Invalid: " . $result['message'];
}

// Validate a single subdivision
$result = $validator->validateSubdivision('US-CA', 'US');
if ($result['valid']) {
    echo "Valid subdivision";
}

// Validate multiple countries
$results = $validator->validateCountries(['US', 'CA', 'MX']);
foreach ($results as $result) {
    echo $result['code'] . ': ' . ($result['valid'] ? 'Valid' : 'Invalid');
}

// Validate multiple subdivisions
$results = $validator->validateSubdivisions(
    ['US-CA', 'US-NY', 'US-TX'],
    'US'
);
```

### Laravel Validation Rules

#### Configuration

Add to your `config/services.php`:

```php
'countriesdb' => [
    'private_key' => env('COUNTRIESDB_PRIVATE_KEY'),
    'api_url' => 'https://api.countriesdb.com',
],
```

#### Using Validation Rules

```php
use CountriesDB\Validator\Rules\ValidCountry;
use CountriesDB\Validator\Rules\ValidSubdivision;

// In a FormRequest
public function rules()
{
    return [
        'country' => ['required', new ValidCountry()],
        'subdivision' => ['required', new ValidSubdivision('country')],
    ];
}

// With options
public function rules()
{
    return [
        'country' => ['required', new ValidCountry(followUpward: true)],
        'subdivision' => [
            'required',
            new ValidSubdivision(
                countryAttribute: 'country',
                followRelated: true,
                allowParentSelection: true
            ),
        ],
    ];
}
```

## API Reference

### `Validator`

Main validator class.

#### Constructor

```php
new Validator(string $apiKey, ?string $backendUrl = null)
```

**Parameters:**
- `apiKey` (required): Your CountriesDB API key
- `backendUrl` (optional): Backend API URL (defaults to `https://api.countriesdb.com`)

#### Methods

##### `validateCountry(code, followUpward?)`

Validate a single country code.

**Parameters:**
- `code` (string): ISO 3166-1 alpha-2 country code
- `followUpward` (bool): Check if country is referenced in a subdivision (default: `false`)

**Returns:** `array` with `valid` (bool) and optional `message` (string|null)

##### `validateCountries(codes)`

Validate multiple country codes.

**Parameters:**
- `codes` (array): Array of ISO 3166-1 alpha-2 country codes

**Returns:** `array` of results with `code`, `valid`, and optional `message`

##### `validateSubdivision(code, country, followRelated?, allowParentSelection?)`

Validate a single subdivision code.

**Parameters:**
- `code` (string|null): Subdivision code (e.g., 'US-CA') or null/empty string
- `country` (string): ISO 3166-1 alpha-2 country code
- `followRelated` (bool): Check if subdivision redirects to another country (default: `false`)
- `allowParentSelection` (bool): Allow selecting parent subdivisions (default: `false`)

**Returns:** `array` with `valid` (bool) and optional `message` (string|null)

##### `validateSubdivisions(codes, country, allowParentSelection?)`

Validate multiple subdivision codes.

**Parameters:**
- `codes` (array): Array of subdivision codes or null/empty strings
- `country` (string): ISO 3166-1 alpha-2 country code
- `allowParentSelection` (bool): Allow selecting parent subdivisions (default: `false`)

**Returns:** `array` of results with `code`, `valid`, and optional `message`

### Laravel Rules

#### `ValidCountry`

Laravel validation rule for country codes.

```php
new ValidCountry(bool $followUpward = false)
```

#### `ValidSubdivision`

Laravel validation rule for subdivision codes.

```php
new ValidSubdivision(
    string $countryAttribute = 'country',
    bool $followRelated = false,
    bool $allowParentSelection = false
)
```

## Examples

### End-to-end samples

A full Laravel application example using this package is available in the [countriesdb/examples](https://github.com/countriesdb/examples) repository:

- [`php/backend-laravel`](https://github.com/countriesdb/examples/tree/main/php/backend-laravel) – Full Laravel application using this package's validation rules (`ValidCountry` and `ValidSubdivision`)

The example includes setup instructions and demonstrates all validation scenarios with working API routes.

### Manual Validation in Controllers

If you prefer to validate manually in your controllers instead of using Laravel validation rules:

```php
use CountriesDB\Validator\Validator;

$validator = new Validator(config('services.countriesdb.private_key'));

// In your controller
$country = $request->input('country');
$subdivision = $request->input('subdivision');

$countryResult = $validator->validateCountry($country);
if (!$countryResult['valid']) {
    return response()->json(['error' => $countryResult['message']], 422);
}

$subdivisionResult = $validator->validateSubdivision($subdivision, $country);
if (!$subdivisionResult['valid']) {
    return response()->json(['error' => $subdivisionResult['message']], 422);
}

// Validation passed
```

## Requirements

- PHP 8.0+
- Laravel 9.0+ (or illuminate/http ^9.0|^10.0|^11.0|^12.0)
- Valid CountriesDB API key

## License

Proprietary (NAYEE LLC)

Copyright (c) NAYEE LLC. All rights reserved.

This software is the proprietary property of NAYEE LLC. For licensing inquiries, please contact [NAYEE LLC](https://nayee.net).









