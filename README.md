# Dovetail

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/dovetail?style=flat)](https://packagist.org/packages/decodelabs/dovetail)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/dovetail.svg?style=flat)](https://packagist.org/packages/decodelabs/dovetail)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/dovetail.svg?style=flat)](https://packagist.org/packages/decodelabs/dovetail)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/dovetail/integrate.yml?branch=develop)](https://github.com/decodelabs/dovetail/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/dovetail?style=flat)](https://packagist.org/packages/decodelabs/dovetail)

### Comprehensive config solution for PHP

Dovetail provides a simple, flexible and powerful way to manage configuration data in PHP applications.

---

## Installation

Install via Composer:

```bash
composer require decodelabs/dovetail
```

## Usage

### Env

Dovetail utilises [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) to load environment variables from a `.env` file in your project root. This is automatically loaded when you first access the Dovetail service.

```php
use DecodeLabs\Dovetail\Env;

$dbHost = Env::asString('DB_HOST', 'localhost'); // String
$dbPort = Env::asInt('DB_PORT', 3306); // Int
$debug = Env::asBool('DEBUG', false); // Bool
$test = Env::asString('TEST', 'default'); // Mixed
```

Use `Env::try*()` methods to avoid throwing exceptions when the environment variable is not set and no default value is provided.

### Config

Dovetail provides structures to allow loading config files from any custom location, into `Repository` container tree objects, and presented in domain specific `Config` objects which can then provide custom data access methods according to your needs.

Sensitive data should be loaded from a `.env` file and not stored in config files - use the `Env::as*()` and `Env::try*()` methods to inject these values into your config.

```php
# config/database.php
use DecodeLabs\Dovetail\Env;

return [
    'adapter' => 'mysql',
    'host' => Env::asString('DB_HOST', 'localhost'),
    'port' => Env::asInt('DB_PORT', 3306),
];
```

```php
# app/Config/Database.php
use DecodeLabs\Dovetail\Config;
use DecodeLabs\Dovetail\ConfigTrait;

class Database implements Config
{
    use ConfigTrait;

    public function getAdapter(): string
    {
        return $this->data['adapter'] ?? 'mysql';
    }

    public function getHost(): string
    {
        return $this->data['host'] ?? 'localhost';
    }

    public function getPort(): int
    {
        return $this->data['port'] ?? 3306;
    }
}
```

```php
use DecodeLabs\Dovetail;
use DecodeLabs\Monarch;

$dovetail = Monarch::getService(Dovetail::class);
$config = $dovetail->load('database');
$adapter = $config->getAdapter(); // 'mysql'
```

## Licensing

Dovetail is licensed under the proprietary License. See [LICENSE](./LICENSE) for the full license text.
