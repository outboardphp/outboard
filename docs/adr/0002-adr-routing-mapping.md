# ADR-0002: Explicit Routing Mapping

We have decided to map Actions to Responders via Explicit Routing, where the route definition explicitly maps the URL to *both* the Action and the Responder.

## Context

The framework provides a generic `Dispatcher` (ADR-0001) that needs to know which Responder to use after an Action executes. We considered:
1. **Explicit Routing:** The route defines the URI -> Action + Responder.
2. **Action Metadata:** The Action class declares its Responder via attributes or interfaces.
3. **Convention:** The Dispatcher assumes the Responder name based on the Action name.

## Decision

We chose **Explicit Routing**.

## Consequences

- The Action class is fully decoupled from the Responder. It has no knowledge of how its payload will be presented.
- The same Action can be reused across different routes with different Responders (e.g. an HTML responder for web routes, a JSON responder for API routes).
- This aligns with the purist ADR philosophy implemented in Paul M. Jones's own Radar framework.
