# Outboard

**WORK IN PROGRESS - PRE-ALPHA**

**Outboard** is a new framework ecosystem designed to blend the structural purity of the Action-Domain-Responder (ADR) pattern and PSR-15 middleware with the developer experience (DX) and modern frontend support expected from top-tier frameworks. 

It takes inspiration from:
- **Slim & Mezzio:** For the minimalist, standards-compliant (PSR-15, PSR-7) HTTP foundation.
- **ADR & Radar:** For the strict separation of HTTP routing, business logic, and presentation logic.
- **Symfony & Yii3:** For robust, decoupled, and highly modular internal architectures.
- **Laravel:** For its incredible focus on developer experience, ergonomic developer APIs, and first-class modern frontend integration (Inertia.js, Vite).

## Core Principles

1. **ADR & PSR-15 Native:** Pure, boilerplate-free action-domain-responder flow orchestrated by a generic Dispatcher.
2. **Standards-compliant:** Complete compatibility with PSR standards
3. **Avoids "magic":** Code will be explicit and therefore understandable by humans and automated tooling alike. No Laravel Facades here.
4. **Off-the-shelf First:** Leverage robust, existing community libraries (e.g., Doctrine, Twig) as the default choices before writing first-party solutions. We build standalone tools only when we have a unique vision or when existing tools don't map cleanly to our architecture.
5. **Dual Focus (API & Full-Stack):** Equal, first-class treatment for stateless JSON APIs and rich frontend applications (React, Vue, Svelte via Inertia.js, or vanilla HTMX). The framework must not force developers into a single paradigm.
6. **Balanced Modularity:** The core HTTP request lifecycle lives in a cohesive `framework` package to prevent "version matrix hell", but isolated concerns (like Dependency Injection, Events, or the Inertia bridge) become independent first-party packages.
7. **DX-focused:** A top-tier developer experience contributes to loyalty and organic spread
   - Part of DX is providing high-quality app skeletons, as that is how most developers will be introduced to the ecosystem

## Ecosystem Architecture

The Outboard monorepo is divided into three layers:

- **Framework & Core (`packages/framework`)**: The beating heart of the ecosystem. Contains the Kernel, Router integration, and generic ADR Dispatcher.
- **First-Party Packages (`packages/`)**: Standalone tools built by the Outboard team. Examples include `dic` (Dependency Injection), `wake` (Events), and `inertia-bridge` (Inertia.js integration).
- **App Skeletons (`apps/`)**: Concrete starter kits representing opinionated ways to assemble the framework and packages (e.g., `basic-skeleton`, `api-skeleton`, `inertia-skeleton`).

### Links

#### App Skeletons
- Basic [[path](https://github.com/outboardphp/outboard/tree/main/apps/basic-skeleton)] [[repo](https://github.com/outboardphp/basic-app-skeleton)]

#### Packages
- Framework [[path](https://github.com/outboardphp/outboard/tree/main/packages/framework)] [[repo](https://github.com/outboardphp/framework)]
- PSR-11 Dependency Injection Container [[path](https://github.com/outboardphp/outboard/tree/main/packages/dic)] [[repo](https://github.com/outboardphp/di)]
- Wake (PSR-14 Event Dispatcher) [[path](https://github.com/outboardphp/outboard/tree/main/packages/wake)] [[repo](https://github.com/outboardphp/wake)]

---

## Ecosystem Boundaries: Framework vs. Skeleton

The framework package should own **how an Outboard application runs**. The app skeleton should own **what a particular application is**.

A good default rule is:
- If a second or third Outboard app would likely reuse something unchanged, it probably belongs in the framework package.
- If something primarily reflects one application's needs, preferences, infrastructure, or examples, it belongs in the app skeleton.

### The framework package should own
Reusable runtime behavior, contracts, default integration points, and bootstrapping concerns that are meaningful across many Outboard applications.
*Examples:* the application/kernel lifecycle, container bootstrapping and definition aggregation, middleware pipeline assembly, request handling flow, router integration contracts and default adapters, ADR-oriented dispatch abstractions, response emission strategy.

### The app skeleton should own
Project-specific composition, concrete application code, and opinionated examples that are meant to be customized or replaced.
*Examples:* the front controller entry file, local DI definitions, routes for that app, actions, responders, domain services, and repositories, templates and assets, environment-specific configuration, logging, database, cache, mailer, ORM, and templating choices.

### Important boundary rule
The framework may provide defaults, but those defaults should be **reusable and overridable**.
The skeleton may provide defaults too, but those defaults are **opinionated and disposable**.

### Decision table

| Concern | Belongs in `packages/framework` | Belongs in `apps/basic-skeleton` |
| :-- | :-- | :-- |
| Kernel / application lifecycle | Yes | No |
| DI definition aggregation for framework services | Yes | No |
| Tiny web entry point that boots the app | No | Yes |
| Middleware pipeline interfaces and default assembly | Yes | No |
| Route table for a particular app | No | Yes |
| Action / responder abstractions reused across apps | Yes, if truly generic | No, if app-specific |
| Concrete actions and responders | No | Yes |
| HTTP factories, emitter integration, and request handling defaults | Yes | No |
| Template files and view layer setup | No | Yes |
| Database credentials and persistence wiring | No | Yes |
| Logger, mailer, cache, ORM selection | No | Yes |
| Event lifecycle hooks and integration points | Yes | No |
| App-specific event listeners | No | Yes |
| Documentation for using the framework runtime | Yes | No |
| Example application structure and conventions | No | Yes |

### What should not move into the framework prematurely

The framework package should resist absorbing the following too early:
- route tables for real applications
- domain services and business logic
- ORM-specific repositories and entities
- concrete logger, template engine, cache, or mailer choices as hard requirements
- environment variable layout for a specific deployment target
- app-specific error pages or UI conventions
- demo controllers, actions, or responders that are not actually reusable abstractions

Those things are valuable, but they belong in the skeleton unless and until a clearly reusable abstraction emerges.
