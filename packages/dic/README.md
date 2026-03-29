# Outboard DI Container

A lightweight, composable IoC/dependency injection container for PHP, usable on its own or as part of the Outboard Framework.

This is the `outboardphp/di` package from the Outboard monorepo. It can be used standalone or as part of the full framework.

## Design principles
- Respect SOLID principles, especially SRP, more than most other DI libraries
- Use a minimum of "magic" so that it is straightforward to understand
- Be powerful, flexible, and feature-rich, yet also fast and efficient
- Support a modular/layered architecture, allowing for simplified configuration by multiple packages
- Build on the work of [Level-2/Dice](https://github.com/Level-2/Dice), updating it for modern PHP and adding a few
  features from other libraries

## Status

Pre-alpha. Stable enough to experiment with, but APIs may still evolve.
Feel free to try it and report bugs.

Implemented features:
- Service retrieval and factory-style creation (`get()` / `make()`)
- Shared and non-shared lifecycles (`shared`, prototype-by-default)
- Constructor parameter overrides (`withParams()` by name or position)
- Callable invocation with dependency resolution (`call()`)
- Optional autowiring via resolver strategy (`AutowiringResolver`)
- Definition inheritance across class/interface hierarchies (`strict` to opt out)
- Parent-container fallback for dependency resolution (`setParent()`)

Current missing features (planned):
- Service tagging retrieval (`getTagged()` / tag queries)
- Type-based parameter matching in `withParams`
- Lazy services / lazy proxies
- Runtime circular dependency detection with cycle-path errors
- Parameter resolution for container-wide config values
- Parameterized `make()` runtime constructor overrides

## Requirements

- PHP `>=8.4`
- Composer `2.x`

## Installation

```bash
composer require outboardphp/di
```

## Quick Start

Preferred usage is via `ContainerFactory` with a `DefinitionProvider`.
You can implement `DefinitionProvider` in your own way to achieve
a variety of strategies of loading definitions.

```php
<?php

declare(strict_types=1);

use Outboard\Di\ContainerFactory;
use Outboard\Di\Contract\DefinitionProvider;
use Outboard\Di\ValueObject\Definition;

$provider = new class implements DefinitionProvider {
    public function getDefinitions(): array
    {
        return [
            PDO::class => new Definition(
                withParams: ['dsn' => 'sqlite::memory:'],
                shared: true,
            ),
            DateTimeZone::class => new Definition(
                withParams: ['timezone' => 'UTC'],
                shared: true,
            ),
            'db.healthcheck' => new Definition(
                substitute: static fn(PDO $db) => (int) $db->query('SELECT 1')->fetchColumn(),
            ),
            'app.started_at' => new Definition(
                substitute: static fn(DateTimeZone $tz) => new DateTimeImmutable('now', $tz),
                shared: true,
            ),
        ];
    }
};

$container = new ContainerFactory($provider)->build();

$db = $container->get(PDO::class);
var_dump($db instanceof PDO); // true
```
If you need lower-level control (for tests or custom bootstrapping), you can still instantiate `Container` directly with Resolver instances.

### About `withParams` matching order

Parameters are resolved in this precedence:
1. **Named keys** (`'paramName' => value`) always override any other resolution
2. **Single-class-typed parameters (not union or intersection types)** are resolved from the container by type
3. **Remaining parameters** consume numeric-keyed `withParams` values in order (as a queue)
4. **Default values** or errors for anything still unresolved

This means you can supply scalar values without keys, and they'll apply to unresolved (non-class-typed) parameters in the order you provide them.

Example:
```php
class Worker {
    public function __construct(
        public Database $db,      // resolved by typehint
        public string $name,      // will get 'alice' from queue
        public int $retries,      // will get 3 from queue
    ) {}
}

$definitions = [
    Worker::class => new Definition(
        withParams: ['alice', 3],  // numeric queue, no keys needed
    ),
];
```

If a string **value** is supplied in `withParams`, it will try to match container definitions first, then fallback to the literal string if no match is found.

## Basic Usage

### 1) Register definitions

Create a `Definition` per service id and pass all definitions into `Resolver`.

```php
$definitions = [
    LoggerInterface::class => new Definition(
        substitute: FileLogger::class,
        shared: true,
    ),
];

$container = new Container([new Resolver($definitions)]);
```

### 2) Shared vs non-shared lifecycle

- `shared: false` (default) -> new instance each `get()` call (prototype behavior)
- `shared: true` -> same instance reused by `get()` (singleton behavior)
- `make(string $id)` -> always returns a fresh instance

```php
$definitions = [
    MyService::class => new Definition(
        shared: true,
    ),
];

$container = new Container([new Resolver($definitions)]);

$a = $container->get(MyService::class); // fresh instance
$b = $container->get(MyService::class); // same instance as $a

$c = $container->make(MyService::class); // different instance than $a or $b
$d = $container->make(MyService::class); // different instance than $a, $b, or $c
```

### 3) Constructor parameters (`withParams`)

You can pass scalar or object constructor parameters by name or queue them by numeric index.

```php
$definitions = [
    ReportWriter::class => new Definition(
        withParams: [
            'path' => '/tmp/report.log',
            3, // will apply to the next unresolved parameter
        ],
    ),
];
```

### 4) Callable substitutes (factory style)

A definition can use a callable for custom creation logic. Class typehints on a callable's parameters will be resolved from the container.

```php
$definitions = [
    'mailer' => new Definition(
        substitute: static fn() => new Mailer('smtp://localhost'),
    ),
];
```

### 5) Post-construction hooks (`call`)

Run additional setup after construction, or return a replacement object (decoration).
These closures will be passed two parameters: the constructed object, and the container.

```php
$definitions = [
    Logger::class => new Definition(
        call: static fn(Logger $logger) => $logger->withChannel('api'),
    ),
];
```

### 6) Invoke arbitrary callables with DI (`Container::call`)

`call()` combines manually supplied args with container-resolved class dependencies.

```php
$result = $container->call(
    static fn(stdClass $obj, int $multiplier) => $obj->value * $multiplier,
    ['multiplier' => 2],
);
```

## Basic Usage: Autowiring Example

If you want implicit class resolution, use the AutowiringResolver factory.

```php
use Outboard\Di\AutowiringResolver;
use Outboard\Di\Container;
use Outboard\Di\ValueObject\Definition;

$definitions = [
    App\Service\UserService::class => new Definition(
        shared: true,
    ),
];
$container = new Container([AutowiringResolver::create($definitions)]);

$service = $container->get(App\Service\UserService::class);
```

If you want to use autowiring class resolution only to cover gaps in your
explicit definitions, you can combine resolvers. Each one will be queried sequentially.

```php
use Outboard\Di\AutowiringResolver;
use Outboard\Di\Container;
use Outboard\Di\Resolver;
use Outboard\Di\ValueObject\Definition;

$container = new Container([
    new Resolver($definitions),
    AutowiringResolver::create($definitions),
]);
```
This is essentially what `ContainerFactory` does by default.

## Core API at a Glance

- `Container::get(string $id): mixed`
- `Container::make(string $id): mixed`
- `Container::call(callable $callable, array $args = []): mixed`
- `Definition` properties: `shared`, `strict`, `substitute`, `withParams`, `call`, `tags`

## What It Does Well Today

- Explicit definitions with predictable behavior
- Optional autowiring strategy
- Regex/class/interface-based matching
- Definition inheritance down class/interface hierarchies (`strict` to opt-out)
- Modular definition composition via providers

## Known Gaps (Planned)

- Tag retrieval APIs (for example, `getTagged()`)
- Lazy proxies
- Runtime cycle-path diagnostics for circular dependencies
- Parameterized runtime overrides in `make()`

## Contributing

Issues and pull requests may be submitted to the main [monorepo](https://github.com/outboardphp/outboard).
Commits there will automatically be reflected here after automatic workflows run.

## History

Previously I started to catalogue the details of many DIC libraries in order to
lay out my opinions on each and synthesize my favorite parts of all of them into
my ideal DIC library. See [this repo's wiki](https://github.com/outboardphp/di/wiki).

But now with the advent of GenAI chatbots, I'm letting computers do that research
for me so I can spend more time on decision-making and writing code.

## Inspiration

The following libraries have aspects I really respect and plan to incorporate here:
- [Dice](https://github.com/Level-2/Dice)
- [Aura.Di](https://github.com/auraphp/Aura.Di)
- [Auryn](https://github.com/rdlowrey/auryn) / [AmPHP Injector](https://github.com/amphp/injector)
- [Caplet](https://github.com/pmjones/caplet)
- [Capsule DI](https://github.com/capsulephp/di)
- [Laminas DI](https://github.com/laminas/laminas-di)
- [Laravel's container](https://github.com/illuminate/container)
- [The PHP League's Container](https://github.com/thephpleague/container)
- [Symfony DI](https://github.com/symfony/dependency-injection)
- [Unbox](https://github.com/mindplay-dk/unbox)
- [Yii 3 DI](https://github.com/yiisoft/di)

## Characteristics
- The container is a runtime repository; definitions are supplied externally rather than registered imperatively on the container.
- Definitions can be composed from multiple providers for modular package-level configuration.
- Resolver strategies are composable: explicit-definition resolution is the baseline, with optional autowiring resolution.
- Definition IDs can be arbitrary strings, class/interface names, or regex patterns for broad matching rules.
- Class/interface definitions inherit down the type hierarchy by default; `strict` disables inheritance for that definition.
- A definition can substitute with a class name, another service ID, a callable factory, or a prebuilt instance.
- Callable substitutes let definitions act as factories for custom object creation flows.
- `withParams` supports constructor overrides for scalars and objects by parameter name or position.
- Autowiring is optional and can be mixed with explicit parameter overrides.
- Services are non-shared by default (prototype-style); enable `shared` for singleton behavior.
- `get()` respects configured sharing/caching, while `make(string $id)` always returns a fresh instance.
- The container can invoke any callable via `call()`, combining explicit args with container-based dependency resolution.
- The container can inject itself when a dependency is type-hinted as the active container type.
- Post-construction `call` hooks support initialization and decoration, including replacing the original instance.
- Parent-container fallback is supported for delegated dependency lookup in composed container graphs.

## Won't Do
Features that I don't intend to implement:
- Explicit setter injection
- Property injection
- A self-binding global container instance
