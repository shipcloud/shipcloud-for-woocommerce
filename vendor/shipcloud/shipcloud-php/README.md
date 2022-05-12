# shipcloud-php-client
 A php client sdk for shipcloud api

- API version: 1.0.0 (https://developers.shipcloud.io/reference/)
- Package version: 0.0.2

## Requirements

PHP 7.4 and later

## Installation & Usage
### Composer

To install the bindings via [Composer](http://getcomposer.org/), add the following to `composer.json`:

```
{
  "repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:shipcloud/shipcloud-php.git"
    }
  ],
  "require": {
    "shipcloud/shipcloud-php": "*"
  }
}
```

Then run `composer install`

### Manual Installation

Download the files and include `autoload.php`:

```php
    require_once('/path/to/shipcloud-php/autoload.php');
```

## Tests

To run the unit tests:

```
composer [install|update]
./vendor/bin/phpunit test
```

## Getting Started

To call API client just create a new ApiClient instance and simply call one of the methods, e.g.:

```
$api = new ApiClient( <YOUR_API_KEY> );
$response = $api->get_me();

echo json_encode( $response );

/*
Output:
{
    "id": "usr-xxxxxxxx",
    "email": "info@example.org",
    "first_name": "John",
    "last_name": "Doe",
    "customer_no": "xxxxxxxx",
    "environment": "sandbox",
    "subscription": {
        "plan_name": "developer",
        "plan_display_name": "Developer",
        "chargeable": false
    }
}
*/
```
