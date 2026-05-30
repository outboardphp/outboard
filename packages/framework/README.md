# Outboard Framework

Currently targeting: PHP 8.4+

Outboard is a new PHP framework that provides a SOLID foundation
for building modern web applications. It is designed to be modular, flexible, and
easy to use, while adhering to the latest best practices in PHP development.

> **Note:** This package provides the low-level, mechanical HTTP request lifecycle. For the high-level vision and phases of the entire Outboard ecosystem (including frontend integration and APIs), see the [Ecosystem README](../../README.md) in the project root.

## Purpose

This package provides the reusable runtime and integration surface for the Outboard ecosystem.

Outboard is built around two primary concepts:
1. A **PSR-15 Middleware Pipeline** (Mezzio-style)
2. The **ADR (Action-Domain-Responder)** paradigm

The `outboardphp/framework` package owns **how an Outboard application runs**. It provides the core Kernel, the generic ADR Dispatcher, and the routing integration. It is designed to be fully agnostic of your application's specific business logic (Domain) and presentation logic (Responder).

At the moment, the framework package is still mostly scaffolding:

- `Outboard\Framework\Application` is currently a placeholder invokable class.
- `Outboard\Framework\ConfigProvider` is currently a placeholder definition provider.

## Architectural Target

The framework package is a small reusable kernel that performs a standard 6-step lifecycle for every request:

1. Assemble framework-owned definitions
2. Merge those definitions with app-provided definitions
3. Build or receive a Dependency Injection Container
4. Resolve the runtime services needed to handle a request
5. Run the middleware / dispatch pipeline
6. Return or emit a response

By owning this mechanical lifecycle, the `framework` package allows individual Outboard app skeletons to remain incredibly thin—containing only their specific routes, DI definitions, and concrete Action/Responder implementations.

## Contributing

Any contributions are welcomed and requested. Help me make this thing awesome!
