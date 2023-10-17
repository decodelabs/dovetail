# Dovetail

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/dovetail?style=flat)](https://packagist.org/packages/decodelabs/dovetail)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/dovetail.svg?style=flat)](https://packagist.org/packages/decodelabs/dovetail)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/dovetail.svg?style=flat)](https://packagist.org/packages/decodelabs/dovetail)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/decodelabs/dovetail/Integrate)](https://github.com/string|int|floatdecodelabs/dovetail/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/dovetail?style=flat)](https://packagist.org/packages/decodelabs/dovetail)

### Comprehensive config solution for PHP

Dovetail provides a simple, flexible and powerful way to manage configuration data in PHP applications.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---

## Installation

Install via Composer:

```bash
composer require decodelabs/dovetail
```

## Usage

### Importing

Dovetail uses [Veneer](https://github.com/decodelabs/veneer) to provide a unified frontage under <code>DecodeLabs\Dovetail</code>.
You can access all the primary functionality via this static frontage without compromising testing and dependency injection.

### Env

Dovetail utilises [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) to load environment variables from a `.env` file in your project root. This is automatically loaded when you first access the Env facade.

```php
use DecodeLabs\Dovetail;

$dbHost = Dovetail::envString('DB_HOST', 'localhost'); // String
$dbPort = Dovetail::envInt('DB_PORT', 3306); // Int
$debug = Dovetail::envBool('DEBUG', false); // Bool
$test = Dovetail::env('TEST', 'default'); // Mixed
```

### Config

Dovetail provides structures to allow loading config files from any custom location, into <code>Repository</code> container tree objects, and presented in domain specific <code>Config</code> objects which can then provide custom data access methods according to your needs.

Sensitive data should be loaded from a `.env` file and not stored in config files - use the <code>Dovetail::env*()</code> methods or <code>$_ENV</code> to inject these values into your config.

```php
# config/database.php
use DecodeLabs\Dovetail;

return [
    'adapter' => 'mysql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    // or
    'port' => Dovetail::envInt('DB_PORT', 3306),
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

$config = Dovetail::load('database');
$adapter = $config->getAdapter(); // 'mysql'
```

## Licensing

Dovetail is licensed under the proprietary License. See [LICENSE](./LICENSE) for the full license text.
