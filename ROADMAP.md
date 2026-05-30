# Outboard Ecosystem Roadmap

_Last updated: May 30, 2026_

> **Note:** This document tracks the forward-looking development phases and temporal goals for the Outboard ecosystem. For the static project vision, core principles, and architectural boundaries, please refer to the [README.md](./README.md).

## Development Phases

### Phase 1: Core Foundation & Bootstrap (In Progress)
Establish the underlying engine that powers all Outboard applications.
- Finalize the `outboardphp/framework` config aggregation and application bootstrap lifecycle.
- Implement the PSR-15 middleware pipeline assembly.
- Finalize the generic `Dispatcher` that automates the ADR flow (invoking Actions and delegating to Responders).

### Phase 2: Routing & The ADR Workflow
Connect the HTTP request to the application logic.
- Integrate a robust off-the-shelf router (e.g., Symfony Router).
- Implement **Explicit Routing**, where a route definition maps a URI to both an Action and a Responder.
- Define the standard `DomainPayload` contract to ensure the Domain remains entirely decoupled from HTTP concerns.

### Phase 3: The "API Platform" Experience
Ensure building JSON APIs is a first-class, ergonomic experience.
- Build robust `JsonResponder` defaults.
- Implement content-negotiated error handling middleware (returning appropriate JSON errors vs HTML error pages).
- Establish recommended patterns for input validation and mapping requests to Data Transfer Objects (DTOs).

### Phase 4: The Modern Full-Stack Experience
Embrace modern frontend tooling without coupling the backend.
- Create `outboardphp/inertia-bridge` as a first-party package to provide seamless Inertia.js support (React/Vue/Svelte).
- Provide out-of-the-box support for Vite integration in the official skeletons.
- Implement server-side templating (Twig/Blade equivalents) responders to support HTMX and traditional vanilla workflows.

### Phase 5: Developer Experience (DX) & Ecosystem
Lower the barrier to entry and maximize developer productivity.
- Develop CLI tooling (generators for Actions, Responders, middleware).
- Establish database/ORM integration recommendations (drawing influence from Yii3 and Doctrine).
- Polish and release distinct starter skeletons for specific workflows (API-first vs. Inertia-first).
