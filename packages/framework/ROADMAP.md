# Outboard Framework Roadmap

_Last updated: May 30, 2026_

> **Note:** This roadmap is scoped specifically to the internal mechanics of the `outboardphp/framework` package. For the high-level vision and phases of the entire Outboard ecosystem (including frontend integration and APIs), see the [Ecosystem Roadmap](../../ROADMAP.md) in the project root.

## Purpose

This document describes what the `outboardphp/framework` package should grow into and in what order.

Outboard is intended to center on a PSR-15 middleware pipeline and the ADR (Action-Domain-Responder) paradigm. The framework package provides the reusable runtime and integration surface for that architecture.

At the moment, the framework package is still mostly scaffolding:

- `Outboard\Framework\Application` is currently a placeholder invokable class.
- `Outboard\Framework\ConfigProvider` is currently a placeholder definition provider.
- The basic skeleton still manually wires the container in `apps/basic-skeleton/public/index.php`.
- The skeleton's `App\ConfigProvider` is also still a stub.

That is normal for this stage. The next goal is not to add lots of app features inside the framework package, but to establish the core mechanical flow.

## Near-term architectural target

The framework package should evolve into a small reusable kernel that can:

1. assemble framework-owned definitions
2. merge those definitions with app-provided definitions
3. build or receive a container
4. resolve the runtime services needed to handle a request
5. run the middleware / dispatch pipeline
6. return or emit a response

The basic skeleton should then become thinner:

1. declare app definitions
2. declare app routes and app services
3. provide a minimal front controller
4. hand off execution to the framework runtime

In other words, the skeleton should stop manually constructing core runtime pieces once the framework can own that bootstrap path.

## Roadmap phases

### Phase 1: Bootstrap and configuration ownership

**Goal:** Make the framework package responsible for reusable bootstrapping.

#### Scope

- Implement `Outboard\Framework\ConfigProvider` as a real definition provider.
- Decide how framework and app definition providers are combined.
- Standardize how the framework discovers or receives app-level definitions.
- Give `Outboard\Framework\Application` a real bootstrap role instead of placeholder output.
- Remove manual framework runtime construction from the skeleton wherever possible.

#### Framework-owned outcomes

- a clear entry point for framework services
- a repeatable definition aggregation story
- a minimal but real application kernel contract
- tests that prove framework bootstrapping works without app-specific logic baked in

#### Exit criteria

- the framework can combine framework and app definitions cleanly
- the skeleton no longer needs to know how core runtime services are built
- there is at least one end-to-end test that proves the boot path works

### Phase 2: HTTP foundation and middleware pipeline

**Goal:** Establish the minimum reusable HTTP runtime in the framework package.

#### Scope

- adopt PSR-7, PSR-15, and PSR-17 dependencies in the framework package
- define how requests are created or received
- define middleware pipeline assembly and execution
- define how a response is produced and emitted
- keep the initial surface area small and explicit

#### Framework-owned outcomes

- reusable request/response lifecycle support
- middleware pipeline assembly owned by the framework
- framework-level service definitions for HTTP runtime pieces
- tests for request handling and middleware ordering

#### Exit criteria

- a request can enter the framework runtime and yield a response
- middleware can be registered without hardcoding app concerns into the framework
- framework tests cover the basic pipeline behavior

### Phase 3: Routing and ADR dispatch

**Goal:** Add the first real application dispatch story.

#### Scope

- introduce routing in the framework package
- likely ship a Symfony Routing-based adapter first
- define how a matched route maps to an action
- define the minimum ADR workflow needed for the first vertical slice
- keep domain and responder implementations app-owned unless a generic abstraction is clearly reusable

#### Framework-owned outcomes

- route matching integration
- route result handoff into action dispatch
- generic dispatcher behavior for request-to-action flow
- extension points for route params and action resolution

#### Exit criteria

- one request can be routed to one app action and produce one response
- the framework owns the generic route-dispatch lifecycle

### Phase 4: Extension points and first-party integrations

**Goal:** Add reusable seams, not hardcoded app choices.

#### Scope

- lifecycle events and optional Wake integration
- error handling strategy
- configuration conventions for optional packages
- framework-level interfaces or adapters for templating, logging, or other common concerns only where there is clear cross-app value

#### Framework-owned outcomes

- stable hooks for extension
- reusable integration seams rather than one-off app decisions
- framework documentation for overriding defaults

#### Exit criteria

- framework integrations remain optional and replaceable

## Immediate next implementation slice

The smallest worthwhile next slice is:

1. make `Outboard\Framework\ConfigProvider` a real provider of framework-owned definitions
2. make `Outboard\Framework\Application` orchestrate the reusable bootstrap path
3. have the basic skeleton provide only app definitions and a tiny `public/index.php`
4. prove the flow with one end-to-end path through the framework runtime

A good first acceptance target is:

- the skeleton front controller delegates to framework bootstrap
- framework and app definitions are combined in a standard way
- one app action can be resolved through the container
- one request can be handled without the skeleton needing to build the runtime manually

## Implementation order recommendation

If implementation begins immediately, work in this order:

1. **Definition aggregation and bootstrap ownership**
2. **Minimal HTTP runtime and middleware pipeline**
3. **Router integration and first ADR dispatch path**
4. **Error handling and lifecycle hooks**

This order keeps the framework focused on reusable runtime value first and avoids building a rich skeleton on top of a runtime that is still undefined.
