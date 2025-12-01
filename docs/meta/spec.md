# Dovetail — Package Specification

> **Cluster:** `data`
> **Language:** `php`
> **Milestone:** `m3`
> **Repo:** `https://github.com/decodelabs/dovetail`
> **Role:** Configs

This document describes the purpose, contracts, and design of **Dovetail** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Dovetail in their own applications or libraries.
- Contributors **maintaining or extending** Dovetail.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Dovetail provides a simple, flexible and powerful way to manage configuration data in PHP applications. It offers environment variable loading and type-safe access, configuration file loading into repository structures, domain-specific config objects with custom access methods, template-based config generation, and integration with Genesis build system. It's designed to be a comprehensive configuration solution that separates sensitive data (in `.env` files) from application configuration (in config files).

### 1.2 Non-Goals

Dovetail does **not**:

- Provide validation logic — see Lucid package for validation
- Handle configuration encryption — it's a plain config loader
- Provide configuration UI or editors — it's a backend library
- Handle configuration versioning — it's a static loader
- Provide configuration merging strategies — it loads single files
- Handle configuration inheritance — configs are independent
- Provide configuration hot-reloading — configs are loaded once
- Handle configuration distribution — it's a local file loader

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `data` (see Chorus taxonomy)
- Dovetail is a data package that provides configuration management for the Decode Labs ecosystem. It sits in the data cluster alongside other data access utilities. It depends on Archetype, Atlas, Collections, Coercion, Exceptional, Fluidity, Iota, Kingdom, Lucid, Monarch, and vlucas/phpdotenv. It's used throughout the ecosystem for loading application configuration, accessing environment variables, and managing config files.

### 2.2 Typical Usage Contexts

Typical places Dovetail appears:

- Application configuration loading
- Environment variable access
- Database connection configuration
- Service configuration
- Feature flag configuration
- Build-time configuration caching
- Config file generation from templates
- Domain-specific config objects

Dovetail is intended to be used whenever code needs to load and access configuration data in a type-safe and structured way.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Dovetail`
  Main service class implementing `Kingdom\Service`. Provides methods for loading configs and repositories.

- `DecodeLabs\Dovetail\Env`
  Static class for environment variable access. Provides type-safe methods for reading `.env` files with caching support.

- `DecodeLabs\Dovetail\Config`
  Interface for domain-specific config objects. Extends `Fluidity\Cast` and `Kingdom\Service`. Defines contract for config classes.

- `DecodeLabs\Dovetail\ConfigTrait`
  Trait providing config implementation. Handles repository storage, manifest tracking, and service registration.

- `DecodeLabs\Dovetail\Repository`
  Configuration data container. Extends `Collections\Tree` for nested data access with dot notation.

- `DecodeLabs\Dovetail\Finder`
  Interface for finding config files. Defines method for locating config manifests.

- `DecodeLabs\Dovetail\Finder\Generic`
  Generic finder implementation. Searches for config files in a specified directory.

- `DecodeLabs\Dovetail\Loader`
  Interface for loading config files. Defines methods for loading and saving configs.

- `DecodeLabs\Dovetail\Loader\PHP`
  PHP config loader implementation. Loads PHP config files and saves templates as PHP.

- `DecodeLabs\Dovetail\Manifest`
  Config file metadata. Contains name, path, and format information.

- `DecodeLabs\Dovetail\Format`
  Enum for config file formats (DotEnv, Php).

- `DecodeLabs\Dovetail\Template`
  Config template for generating config files. Handles resolvable values and namespace extraction.

- `DecodeLabs\Dovetail\Template\Resolvable`
  Resolvable value wrapper. Parses `{{code}}` syntax for dynamic values in templates.

- `DecodeLabs\Genesis\Build\Provider\Dovetail`
  Genesis build provider for Dovetail files. Scans `.env` and `config/` directory.

- `DecodeLabs\Genesis\Build\Task\DovetailEnvCache`
  Genesis build task for caching environment variables.

### 3.2 Main Entry Points

The main usage pattern is through the `Dovetail` service:

```php
use DecodeLabs\Dovetail;
use DecodeLabs\Monarch;

$dovetail = Monarch::getService(Dovetail::class);
$config = $dovetail->load('database');
```

For environment variables:

```php
use DecodeLabs\Dovetail\Env;

$dbHost = Env::asString('DB_HOST', 'localhost');
$dbPort = Env::asInt('DB_PORT', 3306);
```

---

## 4. Dependencies

### 4.1 Decode Labs

- `decodelabs/archetype` (required)
  Used for resolving config class names and loader classes by format.

- `decodelabs/atlas` (required)
  Used for file operations when saving config files.

- `decodelabs/collections` (required)
  Used for `Repository` extending `Tree` for nested data access.

- `decodelabs/coercion` (required)
  Used for type coercion when reading environment variables.

- `decodelabs/exceptional` (required)
  Used for exception handling when environment variables are missing or operations fail.

- `decodelabs/fluidity` (required)
  Used for `Cast` interface on `Config` for type casting support.

- `decodelabs/iota` (required)
  Used for caching environment variables in dynamic repository.

- `decodelabs/kingdom` (required)
  Used for service interface (`Service`, `ServiceTrait`) and container integration.

- `decodelabs/lucid` (required)
  Used for value sanitization (if needed for future extensions).

- `decodelabs/monarch` (required)
  Used for accessing paths, runtime mode, and build information.

### 4.2 External

- `vlucas/phpdotenv` (required)
  Used for loading `.env` files and parsing environment variables.

### 4.3 Optional Integrations

- `decodelabs/genesis` (dev dependency, conflict <0.14)
  Used for build system integration. Detected at runtime if installed, used for scanning config files and caching environment variables during build.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- Environment variables are loaded once on first access
- Environment variable cache is stored in Iota dynamic repository
- Config files are loaded once and cached in service
- Config classes must implement `Config` interface
- Config classes must use `ConfigTrait`
- Repository name is derived from config class short name
- Config files are located via Finder implementation
- Loader is resolved by format using Archetype
- Templates support `{{code}}` syntax for resolvable values
- Resolvable values can reference `Env::*()` methods
- Resolvable values can reference static methods with namespaces
- Default values are used when config files don't exist (development only)
- Config files are saved as PHP arrays with proper formatting

### 5.2 Input & Output Contracts

**Dovetail Service Operations:**
- `__construct(Finder $finder, Archetype $archetype, Iota $iota)` — Creates service with dependencies
- `provideService(ContainerAdapter $container): static` — Service factory method
- `canLoad(string $name, ?string $interface): bool` — Checks if config can be loaded
- `load(string $name): Config` — Loads config by name (cached)
- `loadRepository(string $name): ?Repository` — Loads raw repository (returns null if not found)
- `getLoaderFor(Manifest $manifest): Loader` — Gets loader for manifest format
- Properties:
  - `Finder $finder` — Config finder
  - `array $configs` — Loaded config cache

**Env Operations:**
- `initialize(Iota $iota): void` — Initializes environment (loads `.env` file)
- `rebuildCache(Iota $iota): array` — Rebuilds environment cache
- `has(string $name): bool` — Checks if variable exists
- `asString(string $name, ?string $default): string` — Gets as string (throws if missing)
- `tryString(string $name, ?string $default): ?string` — Gets as string (returns null if missing)
- `asInt(string $name, ?int $default): int` — Gets as int (throws if missing)
- `tryInt(string $name, ?int $default): ?int` — Gets as int (returns null if missing)
- `asFloat(string $name, ?float $default): float` — Gets as float (throws if missing)
- `tryFloat(string $name, ?float $default): ?float` — Gets as float (returns null if missing)
- `asBool(string $name, ?bool $default): bool` — Gets as bool (throws if missing)
- `tryBool(string $name, ?bool $default): ?bool` — Gets as bool (returns null if missing)

**Config Operations:**
- `getRepositoryName(): string` — Gets repository name (static)
- `getDefaultValues(): array` — Gets default values (static)
- `__construct(Manifest $manifest, Repository $data)` — Creates config with manifest and data
- `getConfigManifest(): Manifest` — Gets config manifest
- `getConfigRepository(): Repository` — Gets config repository
- `provideService(ContainerAdapter $container): static` — Service factory method (from trait)

**Repository Operations:**
- Extends `Collections\Tree` — All tree operations available
- Uses dot notation for nested access (key separator: `.`)

**Finder Operations:**
- `findConfig(string $name): Manifest` — Finds config file manifest

**Loader Operations:**
- `loadConfig(Manifest $manifest): Repository` — Loads config into repository
- `saveConfig(Manifest $manifest, Template $data): void` — Saves template as config file

**Manifest Operations:**
- `exists(): bool` — Checks if config file exists
- `isFormat(string|Format $format): bool` — Checks if format matches
- Properties:
  - `string $name` — Config name
  - `string $path` — Config file path
  - `Format $format` — Config format

**Template Operations:**
- `__construct(array $data)` — Creates template from data
- `getNamespaces(): array` — Gets required namespaces
- `getData(): array` — Gets template data
- `getUseStatements(): ?string` — Gets PHP use statements

**Resolvable Operations:**
- `parse(string $value): string|static` — Parses resolvable syntax
- `__construct(string $code, string|int|float|null $value)` — Creates resolvable
- `getNamespace(): ?string` — Gets namespace
- `getCode(): string` — Gets code
- `getValue(): string|int|float|null` — Gets default value

**Format Operations:**
- `is(string|Format $format): bool` — Checks if format matches
- Cases: `DotEnv`, `Php`

### 5.3 Environment Variable Loading

Environment variables are loaded from `.env` file:
- Loaded once on first access
- Cached in Iota dynamic repository
- Cache invalidated in development mode when `.env` file changes
- Uses vlucas/phpdotenv for parsing
- Variables stored in `$_ENV` superglobal

### 5.4 Config File Loading

Config files are loaded via Finder:
- Default location: `{rootPath}/config/{name}.php`
- Format determined by file extension
- Loaded into `Repository` (Tree structure)
- Cached in service after first load
- Default values used if file doesn't exist (development only)

### 5.5 Config Class Resolution

Config classes are resolved using Archetype:
- Pattern: `Config` interface + name
- Example: `Config` + `database` → `Database` config class
- Class must implement `Config` interface
- Class must use `ConfigTrait`
- Repository name derived from class short name

### 5.6 Template System

Templates support resolvable values:
- Syntax: `{{code}}` or `{{code:default}}`
- Supports `Env::*()` method calls
- Supports static method calls with namespaces
- Extracts namespaces for use statements
- Generates PHP code with proper formatting

### 5.7 Resolvable Syntax

Resolvable values use `{{code}}` syntax:
- `{{Env::asString('DB_HOST')}}` — Environment variable
- `{{MyClass::method()}}` — Static method call
- `{{code:default}}` — With default value
- Parsed into `Resolvable` objects
- Converted to PHP code when saving

### 5.8 Default Values

Config classes can provide default values:
- `getDefaultValues()` static method returns array
- Used when config file doesn't exist
- Only saved in development mode
- Template generated from defaults

---

## 6. Error Handling

- Missing environment variables throw `Exceptional::Runtime` when using `as*()` methods
- Missing environment variables return null when using `try*()` methods
- Missing config files return null from `loadRepository()`
- Missing config files use default values in development mode
- Invalid config class resolution throws `ArchetypeException` (caught in `canLoad()`)
- File operations may throw exceptions from Atlas
- Template parsing handles invalid syntax gracefully (returns original string)

---

## 7. Configuration & Extensibility

- Finder can be customized for different config locations
- Loader can be extended for different formats (currently PHP only)
- Config classes can provide custom access methods
- Default values can be provided per config class
- Environment cache can be rebuilt via `rebuildCache()`
- Template system supports custom resolvable syntax
- Genesis integration for build-time operations

---

## 8. Interactions with Other Packages

### 8.1 Archetype

Dovetail uses Archetype for resolving:
- Config class names from interface + name
- Loader classes from interface + format

This allows flexible naming and format support.

### 8.2 Atlas

Dovetail uses Atlas for file operations when saving config files from templates.

### 8.3 Collections

Dovetail uses Collections' `Tree` class for `Repository`, providing nested data access with dot notation.

### 8.4 Coercion

Dovetail uses Coercion for type conversion when reading environment variables, ensuring type safety.

### 8.5 Exceptional

Dovetail uses Exceptional for all exception handling, providing consistent error reporting across the ecosystem.

### 8.6 Fluidity

Dovetail uses Fluidity's `Cast` interface on `Config`, allowing config objects to be cast to other types.

### 8.7 Iota

Dovetail uses Iota for caching environment variables in a dynamic repository, improving performance and allowing cache invalidation.

### 8.8 Kingdom

Dovetail implements Kingdom's `Service` interface, allowing it to be registered as a service in the Kingdom service container. Config classes also implement `Service` for dependency injection.

### 8.9 Lucid

Dovetail depends on Lucid (though usage is not immediately apparent in the codebase). It may be used for value sanitization in future extensions.

### 8.10 Monarch

Dovetail uses Monarch for:
- Accessing paths (for finding `.env` and config files)
- Checking runtime mode (development vs production)
- Checking build compilation status
- Accessing service container

### 8.11 vlucas/phpdotenv

Dovetail uses vlucas/phpdotenv for loading and parsing `.env` files. It uses `Dotenv::createMutable()` and `safeLoad()` for safe loading without overwriting existing environment variables.

### 8.12 Genesis

Dovetail provides Genesis integration:
- Build provider for scanning `.env` and `config/` files
- Build task for caching environment variables during build

---

## 9. Usage Examples

### 9.1 Environment Variables

```php
use DecodeLabs\Dovetail\Env;

// With defaults
$dbHost = Env::asString('DB_HOST', 'localhost');
$dbPort = Env::asInt('DB_PORT', 3306);
$debug = Env::asBool('DEBUG', false);

// Without defaults (throws if missing)
$apiKey = Env::asString('API_KEY');

// Try methods (returns null if missing)
$optional = Env::tryString('OPTIONAL_VAR');
```

### 9.2 Creating a Config Class

```php
use DecodeLabs\Dovetail\Config;
use DecodeLabs\Dovetail\ConfigTrait;
use DecodeLabs\Dovetail\Env;

class Database implements Config
{
    use ConfigTrait;
    
    public static function getDefaultValues(): array
    {
        return [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'name' => 'myapp',
            'user' => 'root',
            'password' => null
        ];
    }
    
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

### 9.3 Config File

```php
# config/Database.php
use DecodeLabs\Dovetail\Env;

return [
    'adapter' => 'mysql',
    'host' => Env::asString('DB_HOST', 'localhost'),
    'port' => Env::asInt('DB_PORT', 3306),
    'name' => Env::asString('DB_NAME', 'myapp'),
    'user' => Env::asString('DB_USER', 'root'),
    'password' => Env::tryString('DB_PASSWORD')
];
```

### 9.4 Loading Configs

```php
use DecodeLabs\Dovetail;
use DecodeLabs\Monarch;

$dovetail = Monarch::getService(Dovetail::class);

// Load config
$config = $dovetail->load('database');
$adapter = $config->getAdapter();

// Check if can load
if ($dovetail->canLoad('database')) {
    $config = $dovetail->load('database');
}

// Load raw repository
$repo = $dovetail->loadRepository('database');
if ($repo !== null) {
    $host = $repo['host'];
}
```

### 9.5 Repository Access

```php
use DecodeLabs\Dovetail\Repository;

$repo = new Repository([
    'database' => [
        'host' => 'localhost',
        'port' => 3306
    ]
]);

// Dot notation access
$host = $repo['database.host'];
$port = $repo['database.port'];

// Tree operations
$database = $repo->getNode('database');
```

### 9.6 Custom Finder

```php
use DecodeLabs\Dovetail\Finder;
use DecodeLabs\Dovetail\Finder\Generic;
use DecodeLabs\Dovetail\Manifest;
use DecodeLabs\Dovetail\Format;

class CustomFinder implements Finder
{
    public function findConfig(
        string $name
    ): Manifest {
        return new Manifest(
            $name,
            '/custom/path/' . $name . '.php',
            Format::Php
        );
    }
}

$finder = new CustomFinder();
$dovetail = new Dovetail($finder, $archetype, $iota);
```

### 9.7 Template Generation

```php
use DecodeLabs\Dovetail\Template;
use DecodeLabs\Dovetail\Template\Resolvable;

// Template with resolvable values
$template = new Template([
    'host' => '{{Env::asString("DB_HOST", "localhost")}}',
    'port' => '{{Env::asInt("DB_PORT", 3306)}}'
]);

// Get namespaces
$namespaces = $template->getNamespaces();
// ['DecodeLabs\\Dovetail']

// Get use statements
$useStatements = $template->getUseStatements();
// "use DecodeLabs\\Dovetail;\n\n"
```

### 9.8 Resolvable Values

```php
use DecodeLabs\Dovetail\Template\Resolvable;

// Parse resolvable
$resolvable = Resolvable::parse('{{Env::asString("DB_HOST")}}');
// Returns Resolvable instance

$resolvable = Resolvable::parse('{{MyClass::method()}}');
// Returns Resolvable with namespace 'MyClass'

$resolvable = Resolvable::parse('plain string');
// Returns 'plain string'

// Get code
$code = $resolvable->getCode();
// 'Env::asString("DB_HOST")'

// Get namespace
$namespace = $resolvable->getNamespace();
// 'DecodeLabs\\Dovetail'
```

---

## 10. Implementation Notes (for Contributors)

### 10.1 Environment Variable Caching

Environment variables are cached in Iota:
- Cache key: `dovetail.dotenv`
- Cache type: static array
- Invalidated in development when `.env` file changes
- Rebuilt during Genesis build process

### 10.2 Config Class Resolution

Config classes are resolved using Archetype pattern:
- Interface: `Config`
- Name: config name (e.g., `database`)
- Resolved to: `Database` class
- Class must implement `Config` interface
- Class must use `ConfigTrait`

### 10.3 Repository Structure

Repository extends `Collections\Tree`:
- Uses dot notation for nested access
- Key separator: `.`
- Provides tree operations for nested data

### 10.4 Template Parsing

Templates parse resolvable values:
- Pattern: `{{code}}` or `{{code:default}}`
- Extracts namespaces from code
- Generates PHP code when saving
- Handles nested arrays recursively

### 10.5 Resolvable Parsing

Resolvable parsing:
- Pattern: `/^\{\{([^}]+)\}(\:([^}]+))?\}$/i`
- Extracts code and optional default value
- Detects `Env::*()` methods
- Detects namespace patterns
- Returns original string if not resolvable

### 10.6 PHP Export

PHP export generates formatted arrays:
- Detects numeric vs associative arrays
- Properly formats nested arrays
- Handles Resolvable objects
- Generates use statements
- Proper indentation and formatting

### 10.7 Service Registration

Config classes can be registered as services:
- Use `ConfigTrait::provideService()`
- Creates lazy proxy
- Loads config on first access
- Integrates with Kingdom container

### 10.8 Genesis Integration

Genesis integration:
- Provider scans `.env` and `config/` files
- Task caches environment variables during build
- Improves performance in compiled builds

---

## 11. Testing & Quality

- **Code Quality Score:** 4/5
- **README Quality Score:** 3/5
- **Documentation Score:** 0/5 (this spec)
- **Test Coverage Score:** 0/5

See `composer.json` for supported PHP versions.

---

## 12. Roadmap & Future Ideas

- Add more config formats (JSON, YAML, etc.)
- Add config validation
- Add config merging strategies
- Improve documentation and usage examples
- Add test coverage
- Consider adding config inheritance
- Consider adding config hot-reloading
- Consider adding config encryption
- Consider adding config versioning
- Consider adding more resolvable syntax options

---

## 13. References

- [Archetype Package](https://github.com/decodelabs/archetype) — Class resolution
- [Atlas Package](https://github.com/decodelabs/atlas) — File operations
- [Collections Package](https://github.com/decodelabs/collections) — Tree data structure
- [Coercion Package](https://github.com/decodelabs/coercion) — Type conversion
- [Exceptional Package](https://github.com/decodelabs/exceptional) — Exception handling
- [Fluidity Package](https://github.com/decodelabs/fluidity) — Type casting
- [Iota Package](https://github.com/decodelabs/iota) — Dynamic repository caching
- [Kingdom Package](https://github.com/decodelabs/kingdom) — Service container
- [Lucid Package](https://github.com/decodelabs/lucid) — Value sanitization
- [Monarch Package](https://github.com/decodelabs/monarch) — Runtime and paths
- [vlucas/phpdotenv Package](https://github.com/vlucas/phpdotenv) — Environment variable loading
- [Genesis Package](https://github.com/decodelabs/genesis) — Build system
- [Chorus Package Index](../../../chorus/config/packages.json) — Ecosystem metadata

