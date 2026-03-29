# Outboard's Dependency Injection Container

This is an IoC/DI container library for PHP, usable on its own or as part of the Outboard Framework.

## Design principles
- Respect SOLID principles, especially SRP, more than most other DI libraries
- Use a minimum of "magic" so that it is straightforward to understand
- Be powerful, flexible, and feature-rich, yet also fast and efficient
- Support a modular/layered architecture, allowing for simplified configuration by multiple packages
- Build on the work of [Level-2/Dice](https://github.com/Level-2/Dice), updating it for modern PHP and adding a few
  features from other libraries

## Status
Should be usable in its present state. Feel free to try it and report bugs.

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
- Explicit setter injection
- Property injection
- A self-binding global container instance
