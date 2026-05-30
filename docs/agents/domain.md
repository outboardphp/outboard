# Domain Docs

How engineering skills should consume this repo's domain documentation.

## Before exploring, read these

- **`docs/architecture.md`** — this repo's domain doc: component overview, glossary, and
  architectural context. This file plays the role that `CONTEXT.md` plays in other repos.
  Skills that reference `CONTEXT.md` should look here instead.
- **`docs/adr/`** — read ADRs that touch the area you're about to work in.

If either of these doesn't exist yet, **proceed silently**. Don't flag their absence; don't
suggest creating them upfront. The producer skill (`/grill-with-docs`) creates them lazily
as terms and decisions get resolved.

## Layout

Single-context repo. The domain is unified across all packages — `packages/` contains
framework components and `apps/` contains app skeletons, but they share one domain language.

```
/
├── docs/
│   ├── architecture.md   ← domain doc / glossary (replaces CONTEXT.md convention)
│   ├── adr/              ← architectural decision records
│   └── agents/           ← agent configuration (this directory)
├── packages/             ← framework components
└── apps/                 ← app skeletons / starting points
```

## Use the glossary's vocabulary

When your output names a domain concept (in an issue title, a refactor proposal, a hypothesis,
a test name), use the term as defined in `docs/architecture.md`. Don't drift to synonyms the
glossary explicitly avoids.

If the concept you need isn't in the glossary yet, that's a signal — either you're inventing
language the project doesn't use (reconsider) or there's a real gap (note it for `/grill-with-docs`).

## Flag ADR conflicts

If your output contradicts an existing ADR, surface it explicitly rather than silently overriding:

> _Contradicts ADR-0007 — but worth reopening because…_
