# ADR-0001: ADR Dispatch Flow

We are decoupling the PSR-15 `RequestHandlerInterface` from the ADR `Action`. The framework will provide a generic `Dispatcher` that acts as the final request handler in the middleware pipeline; it invokes the Action, takes the returned Domain Payload, passes it to the Responder, and returns the final HTTP Response.

## Consequences

- Actions do not need to implement `RequestHandlerInterface`.
- Actions are not burdened with the boilerplate of manually instantiating or invoking Responders.
- The framework structurally guarantees the ADR flow, since an Action is expected to return a Domain Payload (not a Response), forcing the output through the Responder layer.
