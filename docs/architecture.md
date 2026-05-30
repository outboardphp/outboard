# Architecture & Domain

This document defines the architectural patterns and canonical domain language for the Outboard framework.
It serves as the definitive reference for how components are structured and what terms are used to describe them.

## Architectural Paradigm

Outboard is built on two primary foundations:
1. **Middleware Pipeline**: A PSR-15 compliant pipeline that handles the HTTP request/response lifecycle.
2. **Action-Domain-Responder (ADR)**: A server-side UI pattern for structuring the application logic that runs inside the middleware pipeline.

## Language (Glossary)

The following terms are the canonical vocabulary for the Outboard domain.

### ADR Components

**Action**:
The entry point for a specific HTTP request. It collects input from the request, invokes the Domain, and passes the result to a Responder. It contains no business logic and no presentation logic.
_Avoid_: Controller, Handler

**Domain**:
The core business logic of the application. The Domain is agnostic to HTTP, the web, and presentation. It receives input from the Action, performs work, and returns a Domain Payload.
_Avoid_: Model (when referring to the entire layer)

**Domain Payload**:
A standardized object returned by the Domain to the Action. It encapsulates the result of the domain operation (the data) along with a status indicator (e.g., SUCCESS, NOT_FOUND, INVALID) so the Action knows what happened without needing to catch exceptions for normal control flow.
_Avoid_: Result Object, Response (which means HTTP Response)

**Responder**:
The presentation logic. It receives the Domain Payload from the Action and builds the entire HTTP Response (status code, headers, and body). It is the only component that interacts with templates or formats output (e.g., JSON).
_Avoid_: View, Presenter, ViewModel

**Domain Input (Pattern)**:
Outboard does not enforce a strict format for how Actions pass data into the Domain. However, the recommended best practice is:
- **For simple lookups (e.g., GET requests):** Pass scalar values directly (e.g., `$domain->getUser($id)`).
- **For complex mutations (e.g., POST/PUT requests):** Map the HTTP request into a strongly-typed Data Transfer Object (DTO) or Command object before passing it to the Domain. This prevents the Domain from being coupled to the shape of HTTP request arrays.

### Framework Components

**Middleware**:
A component in the HTTP pipeline that can inspect, modify, or reject a request before passing it to the next middleware, and can inspect or modify the response on the way out.
_Avoid_: Filter, Interceptor

**Container**:
The Dependency Injection Container (PSR-11) that manages object instantiation and lifecycle.
_Avoid_: DIC, Injector

**Dispatcher**:
The generic framework component that implements PSR-15 `RequestHandlerInterface` at the end of the middleware pipeline. It automates the ADR flow by invoking the Action, receiving the Domain Payload, resolving the Responder, and returning the Response.
_Avoid_: Action (when referring to the PSR-15 interface)

**Router**:
The component responsible for matching an incoming HTTP Request to a specific Action and Responder. In Outboard, routing is explicit: the route definition contains the triplet of URI, Action, and Responder.

## Content Negotiation & Presentation

### HTML vs JSON (or other formats)
Outboard's canonical approach for serving different formats (e.g., HTML vs JSON) for the same Domain logic is **Route-Level Separation (Approach A)**. This involves defining separate routes for each format, pointing to the same Action but different Responders (e.g., `/users` maps to `HtmlUserListResponder`, while `/api/users` maps to `JsonUserListResponder`). This is preferred as it allows for cleaner separation of format-specific middleware (like session auth vs. token auth).

However, if an application requires **Content Negotiation (Approach B)**, Outboard permits a single route mapped to a generic Responder that inspects the `Accept` header of the HTTP Request to determine the output format.

### Templating
Responders are the only components permitted to interact with templating engines. The templating engine is injected into the Responder via the Container. 
**Crucial Boundary:** The Domain Payload must *never* contain presentation details, such as template file names. The mapping of a Domain Payload to a specific template is the exclusive responsibility of the Responder.
