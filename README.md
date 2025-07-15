# Outboard
## WORK IN PROGRESS - PRE-ALPHA
We're just getting started. Don't expect too much just yet.

**Outboard** is a new framework based around a
PSR-15 middleware pipeline and the ADR (Action-Domain-Responder) paradigm.

## Goals/Principles
- Robust, cohesive, yet independent first-party packages like Symfony and Yii 3
- Complete compatibility with PSR standards
- Framework architecture that centers around a middleware pipeline, like Mezzio
- An absolute minimum of "magic", _unlike_ some other well-known frameworks
- Use off-the-shelf libraries where a suitable one exists, so this project can focus on what makes it different
- A beautiful API will entice devs and engender loyalty
- A great app skeleton (or two or three) is almost as important as the framework itself

This is a monorepo containing the framework, related packages, and app skeletons.

## App Skeletons
- Basic [[path](https://github.com/outboardphp/outboard/tree/main/apps/basic-skeleton)] [[repo](https://github.com/outboardphp/basic-app-skeleton)]

## Packages
- Framework [[path](https://github.com/outboardphp/outboard/tree/main/packages/framework)] [[repo](https://github.com/outboardphp/framework)]
- PSR-11 Dependency Injection Container [[path](https://github.com/outboardphp/outboard/tree/main/packages/dic)] [[repo](https://github.com/outboardphp/di)]
- Wake (PSR-14 Event Dispatcher) [[path](https://github.com/outboardphp/outboard/tree/main/packages/wake)] [[repo](https://github.com/outboardphp/wake)]
