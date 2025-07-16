# Outboard's Dependency Injection Container

This is an IoC/DI container library for PHP, usable on its own or as part of the Outboard Framework.

## Design principles
- Respect SOLID principles, especially SRP, more than most other DI libraries
- Use a minimum of "magic", so that it is easy to understand
- Be powerful, flexible, and feature-rich, yet also fast and efficient
- Support a modular/layered architecture, allowing for simplified configuration by multiple packages
- Build on the work of [Level-2/Dice](https://github.com/Level-2/Dice), updating it for modern PHP and adding a few
  features from other libraries

## Status
Still working on the architecture.
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
- The container class is merely a repository and does not configure itself. A configuration is supplied to the container.
- Configuration can be supplied by multiple providers, in a modular fashion.
- Uses closures extensively internally and for certain parts of configuration, enabling great flexibility and performance.
- Autowiring can be used but is not required.
- Object creation can be delegated to a user-supplied callable, allowing for custom instantiation logic.
- Like Dice, the internal API eschews typehints in favor of docblocks for performance reasons, but don't worry;
the public API is typehinted for good DX.
- Scalar constructor parameters can be specified in configuration, by position or by parameter name.
- Arbitrary identifiers can be used to refer to instances in configuration, allowing for multiple instances of the same class.
- Like most DI containers, classes/interfaces can have child classes/implementations substituted in their place by configuration.
- Configuration definitions with class/interface identifiers will apply to all child classes/implementations, unless
disabled on a per-definition basis.
- Configuration definition identifiers can be regex patterns, allowing for flexible matching of class/interface names.
- Scoped singletons can be created, allowing for instances to be shared within a specific part of the object tree.
- The container can inject itself into instances. Of course, this should be used carefully and sparingly, but sometimes it is truly needed.
- Any callable can take advantage of the container's autowiring and configuration.
- After instantiation, the container can execute callables on objects for extra initialization logic or decoration, with the
latter allowing replacement of the original instance

## Won't Do
- Explicit setter injection
- Property injection
- A self-binding global container instance
