# Roadmap: Outboard DI Container

This document consolidates planned features, pending architectural work, and intentional non-goals for the `outboardphp/di` package.

---

## Status

Pre-alpha. Core functionality is stable and tested, but APIs may still evolve before a 1.0 release.

What is already implemented:
- Service retrieval and factory-style creation (`get()` / `make()`)
- Shared and non-shared lifecycles (`shared`, prototype-by-default)
- Constructor parameter overrides (`withParams`) by name or position
- Callable invocation with dependency resolution (`Container::call()`)
- Optional autowiring via resolver strategy (`AutowiringResolver`)
- Definition inheritance across class/interface hierarchies (`strict` to opt out)
- Parent-container fallback for dependency resolution (`setParent()`)
- Post-construction hooks and decoration (`call` property)
- Validation container for pre-runtime circular dependency detection
- Regex pattern matching for definition IDs

---

## Planned Features

### High Priority

These are the most commonly needed gaps and are referenced across multiple analysis documents.

#### 1. Service Tagging Retrieval

- `tags` already exists on `Definition`, but there is no retrieval API
- Add `getTagged(string $tag): array` on `Container`
- Add `hasTag(string $tag): bool` convenience method
- Use case: fetching all event listeners, middleware, plugins, etc.

#### 2. Type-Based Parameter Matching in `withParams`

- Currently `withParams` requires a parameter name (string key) or position (int key)
- Add support for interface/class name as key: `[LoggerInterface::class => 'file.logger']`
- The container would scan constructor parameters for a matching type and inject accordingly
- This closes the main contextual binding gap without needing a dedicated fluent API

#### 3. Lazy Services / Lazy Proxies

- Add a `lazy: bool` flag to `Definition`
- Delays instantiation until the first method call
- Useful for expensive services that may not be needed in every request

#### 4. Parameterized `make()` Runtime Overrides

- `make(string $id)` already creates a fresh instance bypassing the shared cache
- Extend the signature to `make(string $id, array $params = []): mixed`
- Allows runtime constructor overrides without a new `Definition`
- Example: `$container->make(Logger::class, ['channel' => 'audit'])`

#### 5. Runtime Circular Dependency Detection with Cycle-Path Errors

- `ContainerFactory` currently detects cycles at validation time
- Add detection at runtime that produces a human-readable cycle path
- Example error message: `"Circular dependency detected: A → B → C → A"`

#### 6. Parameter Resolution (Container-Wide Config Values)

- Allow injecting scalar configuration values from a central config store
- Inspired by Symfony's `%app.debug%` and Aura.Di's `InjectionFactory` params
- Concrete API shape still TBD; could be a dedicated resolver or an extension of `withParams`

---

### Medium Priority

#### 7. `sharedInTree` Implementation

- The `sharedInTree` field already exists on `Definition` but has no resolver support
- Implement scoped singletons: an instance is shared within a single `get()` call's object graph, but not globally
- Requires propagating a `$share` array through the resolution chain

#### 8. Variadic Parameter Support

- Constructors with `...$args` parameters are not currently handled
- The remaining args queue from `withParams` should be spread into the variadic slot
- Also needed for injecting all tagged services as an array

#### 9. Return Type Validation for Callable Substitutes

- When a `substitute` callable has a declared return type, validate it matches the definition's expected type at configuration time
- Also validate the actual returned value at runtime
- Improves error messages for misconfigured factories

#### 10. Service Definition Extension / Post-Registration Decoration

- Allow a definition to be wrapped after initial registration
- Useful for plugin or package architectures where the decorator is registered independently from the original service
- Example: `$container->extend('logger', fn($logger, $c) => new MetricsLogger($logger))`
- Requires a mutation-safe model (e.g., a separate extension registry merged at build time)

#### 11. Explicit Decorator Syntax

- The `call` property already supports decoration by returning a new object, but it is implicit
- A dedicated decorator definition shape would make intent clearer and support:
  - Priority ordering when multiple decorators target the same service
  - Access to both the decorated and original service by ID
- API shape TBD; could be a `Decoration` value object or a property on `Definition`

---

### Low Priority / Exploratory

These are worth tracking but have lower urgency or involve design tradeoffs that have not been fully resolved.

#### 12. Compilation / Container Caching

- Generate optimized PHP code for the container that can be cached to disk
- Eliminates reflection overhead in production
- This is explicitly planned as the **last** major feature to implement, after the API has stabilized
- Inspired by Symfony DI's compiler passes

#### 13. Autowiring by Parameter Name Convention

- When a parameter type is an interface with multiple candidates, fall back to matching by parameter name against service IDs
- Example: `function __construct(LoggerInterface $fileLogger)` would resolve to service `fileLogger`
- Inspired by Symfony DI and Yii 3

#### 14. Scalar Type Autowiring

- Allow `AutowiringResolver` to match scalar parameters (string, int, bool, etc.) from the `$args` queue by type check rather than requiring a name or position
- Currently, scalar parameters without defaults must be in `withParams`

#### 15. Scoped Containers / Child Scopes

- Create child containers that inherit parent definitions but can override them locally
- `sharedInTree` covers part of this use case; full scope support would go further
- Relevant for request/session scopes (already referenced as a `Scope` enum in `Definition`)

#### 16. Multiple Delegate Containers

- `setParent()` supports a single parent as a fallback
- Full PSR-11 delegate pattern would support multiple ordered delegates with proper resolution routing
- Only needed for complex plugin architectures or multi-container compositions

#### 17. Container Freezing

- Add a `freeze()` method that prevents any further state changes after configuration is complete
- Pairs well with the compilation feature
- Lower priority because the container already does not expose public mutation APIs

#### 18. Definition Attributes

- PHP 8 attributes (`#[Service]`, `#[Inject]`, `#[Tag]`) for in-class DI configuration
- **Tradeoff**: attributes are convenient but reduce flexibility—configuration becomes coupled to the class itself and can't be overridden per-environment without fallback logic
- If added, explicit `Definition` config should always take precedence over attribute defaults
- Not planned for early versions; would be added only if there is strong demand

---

## Architectural Work

These items are internal refactors or design decisions rather than user-visible features.

### 1. Unify Callable Invocation (Highest-Value Remaining Improvement)

Currently, callable execution is split across two places:
- `Container::call()` — the general callable invocation path
- `AutowiringParameterApplicator` — which has its own parameter-matching logic
- `ExplicitParameterApplicator` — which delegates to `Container::call()` only when the container is the concrete `Container` class

The goal is a single source of truth for how callables receive named args, queued numeric args, type-resolved deps, defaults, and errors.

Possible shapes:
- A `CallableInvokerInterface` with a `ContainerCallableInvoker` implementation
- A shared callable-parameter resolver used by both the container and the applicators

### 2. Decide: Concrete `Container` as the Only Runtime Container

The current codebase guards on the concrete `Container` type in some paths, which creates a coupling question:

- **Option A**: Lean into `Container` as the only meaningful runtime container; remove or simplify `ComposableContainer` and `CompositeContainer`
- **Option B**: Formalize callable invocation through an interface so that alternative container implementations can participate

This decision shapes how cleanly the callable unification work (above) can be implemented.

### 3. `Resolver` as Orchestration Hub

`Resolver` currently coordinates definition matching, substitution, parameter application, post-call decoration, and resolvability policy. This is reasonable today. If additional behaviors are added, a dedicated factory-assembly pipeline may become worth introducing. Low urgency—watch rather than act on it now.

---

## Won't Implement

The following are explicitly out of scope:

- **Explicit setter injection** — violates immutability and makes dependency graphs harder to reason about
- **Property injection** — same concerns as setter injection
- **Self-binding global container instance** — a service locator anti-pattern; callers should receive the container explicitly
- **Method injection** — outside the intended scope; use `Container::call()` for one-off callable resolution
- **Service Locator Pattern** — explicitly excluded by design philosophy
