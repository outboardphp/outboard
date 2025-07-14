# Outboard Branching Strategy

## Core Philosophy

The Outboard project uses a branching model designed to support a versioned software lifecycle.
This strategy allows us to work on future major releases while simultaneously maintaining older,
stable versions with bug fixes and security patches. This is crucial for providing a reliable and
predictable framework for our users.

This repository has two permanent branches as well as long-lived maintenance branches for each major release.

## Main Branches

### `main`
The `main` branch always points to the **latest stable release**. Code on this branch is tagged with a version number (e.g., `v2.1.4`) and is considered the official, production-ready version of the framework.

* **DO NOT** submit pull requests directly to `main`.
* This branch is only updated by project maintainers during a release.

### `develop`
The `develop` branch is the primary hub for active development on the **next major version**. It contains all the latest features and changes that are not yet in a stable release. This branch represents the future of the framework.

* This is the branch you will target for new features or significant changes.
* Any changes that do not break backward compatibility may be considered for merging into maintenance branches
  for a minor or patch release.
* While we strive for stability, `develop` is a work-in-progress and should not be considered production-ready.

## Maintenance Branches

For each major version of Outboard that is actively supported, we maintain a long-lived maintenance branch.

* **Examples:** `1.x`, `2.x`
* **Purpose:** These branches are used to prepare patch releases (`v1.2.1`) and minor releases (`v1.3.0`) for older, supported versions of the framework.

## How to Contribute

Your contribution workflow will depend on the type of change you are making.

### Contributing a Major Feature or Breaking Change

All major features and changes that include a "breaking change" must be part of the next major release.

1.  **Check out the `develop` branch:**
    ```bash
    git checkout develop
    git pull origin develop
    ```
2.  **Create your feature branch:** Use a descriptive name prefixed with `feature/`.
    ```bash
    git checkout -b feature/my-awesome-new-thing
    ```
3.  **Make your changes, commit, and push.**
4.  **Open a Pull Request** targeting the **`develop`** branch.

### Contributing a Minor Feature or Non-Breaking Change
If your change is a minor feature or a non-breaking change, you can also target the `develop` branch.
Your change may also be considered for merging into maintenance branch(es) for a minor or patch release.
1.  **Check out the `develop` branch:**
    ```bash
    git checkout develop
    git pull origin develop
    ```
2.  **Create your feature branch:** Use a descriptive name prefixed with `feature/`.
    ```bash
    git checkout -b feature/my-minor-update
    ```
3.  **Make your changes, commit, and push.**
4. **Open a Pull Request** targeting the **`develop`** branch.

### Contributing a Bug Fix or Security Patch

When you find a bug, the first step is to identify which versions of the framework are affected.

#### Scenario 1: The bug exists ONLY in the latest development code.

If the bug is only present in the `develop` branch and does not affect any stable releases, the process is simple.

1.  **Branch from `develop`:** Use a descriptive name prefixed with `fix/`.
    ```bash
    git checkout -b fix/bug-in-develop
    ```
2.  **Apply your fix, commit, and push.**
3.  **Open a Pull Request** targeting the **`develop`** branch.

#### Scenario 2: The bug affects a stable (released) version.

This is the most common scenario for bugs and security vulnerabilities.

1.  **Identify the OLDEST supported version affected.** For example, if the bug affects `v1.5.0` and `v2.1.0`, your work should start from the `1.x` maintenance branch.
2.  **Branch from the correct maintenance branch:**
    ```bash
    # Example for a bug affecting the 1.x series
    git checkout 1.x
    git pull origin 1.x
    git checkout -b fix/critical-security-issue
    ```
3.  **Apply your fix, commit, and push.**
4.  **Open a Pull Request** targeting the maintenance branch you branched from (e.g., **`1.x`**).
5.  In your Pull Request description, please mention any other versions (e.g., `2.x`, `develop`) that are also affected.

**What happens next?** Once your fix is approved and merged into the maintenance branch, the project maintainers will handle **cherry-picking** the fix into all other affected branches (e.g., `2.x`, `develop`) to ensure the bug is resolved everywhere. This prevents you from having to create multiple pull requests.

## Quick Reference

| Branch        | Purpose                   | Target For...                                          |
|:--------------| :------------------------ |:-------------------------------------------------------|
| **`main`**    | Latest Stable Release     | Project Maintainers Only                               |
| **`develop`** | Next Major Version        | New Features, Breaking Changes, Bugs only in `develop` |
| **`2.x`**     | Support for v2.x.x        | Bug/Security fixes for v2, non-breaking changes        |
| **`1.x`**     | Support for v1.x.x        | Bug/Security fixes for v1, non-breaking changes        |
