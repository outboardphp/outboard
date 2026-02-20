# Copilot Coding Agent Instructions

## Repository Overview

**Outboard** is a PHP framework monorepo currently in pre-alpha development. It implements a PSR-15 middleware pipeline with ADR (Action-Domain-Responder) architecture and is designed to be modular, SOLID-compliant, and minimal on "magic."

### Technology Stack
- **Language**: PHP 8.4+ (minimum requirement)
- **Framework Type**: Monorepo containing framework packages and app skeletons
- **Package Manager**: Composer 2.x (v2.8.10 verified)
- **Testing**: Pest PHP 3.8 (with PHPUnit extension and architecture tests)
- **Code Quality**: PHPStan (level 9), PHP-CS-Fixer, PHP_CodeSniffer
- **CI/CD**: GitHub Actions
- **Utilities**: Monorepo-builder (Symplify)

### Repository Structure
```
outboard/
├── .docker/                       # Docker configuration
├── .github/workflows/             # CI/CD pipelines (build.yml, split.yml)
├── apps/basic-skeleton/           # Main app skeleton using the framework
├── packages/
│   ├── dic/                       # PSR-11 Dependency Injection Container
│   ├── framework/                 # Core framework (Application, ConfigProvider)
│   └── wake/                      # PSR-14 Event Dispatcher (Mediator/Observer)
├── tests/                         # Monorepo-level test configuration (Pest.php)
├── .php-cs-fixer.dist.php         # Code style rules
├── phpcs.xml.dist                 # CodeSniffer rules (PHP compatibility)
├── phpstan.neon                   # Static analysis config (level 9)
├── phpunit.xml                    # Test suites configuration
├── composer.json                  # Monorepo root configuration
└── monorepo-builder.php           # Monorepo package definitions
```

### Key Files Location
- **Main Packages**: `packages/*/src/`
- **Package Tests**: `packages/*/tests/`
- **App Skeletons**: `apps/*/`
- **Config/Styling**: Root directory contains all validation config files
- **Autoload**: Root `composer.json` defines PSR-4 namespaces for all packages

## Build & Validation Instructions

### Prerequisites
- **PHP**: 8.4+ (verify with `php --version`)
- **Composer**: 2.x (verify with `composer --version`)
- All commands must be run from the monorepo root (`/Users/garrett.whitehorn/code/outboard/`)

### Step 1: Install Dependencies (Always Required First)
```bash
composer install --prefer-dist --no-progress
```
**Time**: 30-60 seconds (first run may take longer if cache is empty)
**Important**: This step is **always required** before running tests, linting, or static analysis. The monorepo uses path repositories with symlinks, so composer must establish these links.
**Verification**: Check that `vendor/autoload.php` exists and symlinks in `vendor/outboardphp/` point to `packages/*/`.

### Step 2: Composer Validation
```bash
composer validate --strict
```
**What it checks**: 
- JSON structure validity
- Version constraints consistency
- Package dependency declarations
**Expected output**: `./composer.json is valid`

### Step 3: Code Style Linting (Check Only)
```bash
composer run-script lint
```
**What it does**: 
- Runs PHP_CodeSniffer (phpcs) for PHP compatibility
- Runs PHP-CS-Fixer in dry-run mode to show formatting issues
**Fails if**: Code doesn't match PER-CS standard with custom rules
**Files checked**: `apps/basic-skeleton/src`, `packages/*/src` only (not tests)

### Step 4: Automatically Fix Code Style Issues
```bash
composer run-script fix
```
**What it does**: 
- Runs `phpcbf` (PHP CodeSniffer fixer) for compatibility issues
- Runs `php-cs-fixer fix` to apply formatting rules
**Always safe**: This modifies files automatically, no dry-run needed
**Use when**: After making code changes if linting fails

### Step 5: Static Analysis with PHPStan
```bash
vendor/bin/phpstan analyse
```
**Level**: 9 (strictest)
**Files checked**: `apps/basic-skeleton/src`, `packages/*/src` (bootstrap: `vendor/autoload.php`)
**Common issues**: 
- Missing type declarations
- Incorrect parameter/return types
- Unused parameters
**Fails if**: Any level 9 violations exist (must be fixed)

### Step 6: Run Tests
```bash
composer run-script test
```
**Framework**: Pest PHP 3.8 (with PHPUnit 10+)
**Test suites** (defined in `phpunit.xml`):
- `DI`: `packages/dic/tests/`
- `Framework`: `packages/framework/tests/`
- `Wake`: `packages/wake/tests/`
**Flags used**: `--compact --display-deprecations --display-phpunit-deprecations`
**Architecture checks**: Tests also run architecture analysis via `arch()` helpers
**Expected**: All tests pass with 0 errors
**Time**: 5-15 seconds depending on system

### Complete Validation Pipeline (Recommended Before PR)
```bash
# 1. Install/update dependencies
composer install --prefer-dist --no-progress

# 2. Validate composer.json
composer validate --strict

# 3. Fix code style automatically
composer run-script fix

# 4. Run linting to verify
composer run-script lint

# 5. Run static analysis
vendor/bin/phpstan analyse

# 6. Run all tests
composer run-script test
```

**Note**: Always run in this order. Tests depend on linting passing, which depends on proper code style.

### GitHub Actions CI Pipeline
The repository runs automated checks on all PRs targeting `main` and `*.x` branches:
1. **Change Detection**: Determines if Docker or PHP changes were made
2. **Docker Build** (if `Dockerfile` or `compose.yml` changed): Validates Docker image builds
3. **PHP Tests** (if `*.php`, `composer.*`, `phpunit.xml`, `phpstan.neon`, or `Pest.php` changed):
   - Validates `composer.json --strict`
   - Installs dependencies with Composer caching
   - Runs `composer run-script test`

**Important**: If tests pass locally but fail in CI, check for:
- Differences in PHP versions (CI uses PHP 8.4)
- Missing `--strict` validation
- Uncommitted `composer.lock` changes
- Stale Composer cache

## Code Quality Standards

### PHP-CS-Fixer Configuration
Uses the **PER-CS** ruleset (PSR-12 evolution) with additional stricter rules:
- PSR-5 aligned multiline comments
- `DateTimeImmutable` over `DateTime`
- No `global` keyword
- Yoda style disabled for most operators
- Single quotes for simple strings
- Many modern PHP 8+ transformations

**Key rules** affecting agents: `single_quote`, `array_push`, `native_function_casing`, `return_assignment`

### PHPStan Analysis (Level 9)
**Strictest setting**. Requires:
- Full type declarations on all function/method parameters and returns
- No mixed types
- No undefined variables or methods
- No loose comparisons
- Exception safe code

**Bootstraps**: `vendor/autoload.php` (monorepo autoloader)

### PHP CodeSniffer (PHP Compatibility)
Checks against **PHP 8.4 compatibility**. Will catch:
- Use of removed PHP functions
- Deprecated syntax
- Compatibility issues with PHP 8.4

## Dependency Management

### Monorepo Package Dependencies
The root `composer.json` defines:
- **Require**: PSR interfaces (`psr/container`, `psr/event-dispatcher`), utilities
- **Require-dev**: Testing, linting, static analysis tools
- **Repositories**: Path repositories for local packages with symlink option
- **Replace**: Provides version aliases (e.g., `outboardphp/di` replaced by monorepo version)
- **Autoload**: PSR-4 mapping for all packages in single namespace tree

**Key constraint**: All packages specify `php: ">=8.4"` minimum

### Adding New Dependencies
1. Modify the appropriate `composer.json` (root for shared, package-specific for package deps)
2. Run `composer install`
3. Run `composer run-script test` to ensure no breaks
4. Check that `composer.lock` is updated and committed

### Security Advisories
`roave/security-advisories:dev-latest` is included. Run `composer install` to check for known CVEs.

## Important Patterns & Constraints

### Architecture Rules (From `tests/Pest.php`)
The codebase enforces:
1. **All classes must be namespaced** (no global namespace)
2. **No abstract classes outside special cases** (architecture test)
3. **No final classes** (except where explicitly needed)

These are validated by Pest architecture tests that run as part of `composer run-script test`.

### Namespace Structure
```
Outboard\Di\               → packages/dic/src/
Outboard\Di\Tests\         → packages/dic/tests/
Outboard\Framework\        → packages/framework/src/
Outboard\Framework\Tests\  → packages/framework/tests/
Outboard\Wake\             → packages/wake/src/
Outboard\Wake\Tests\       → packages/wake/tests/
```

### Test Bypass-Finals Extension
The test suite uses `dg/bypass-finals` to allow testing of classes that use `final` keyword. This means tests can verify final class behavior, so treat `final` carefully—it's a design decision that tests will validate.

### Branching Strategy
- `main`: Production-ready releases only (tagged with version numbers)
- `develop`: Next major version development (not currently active per BRANCHING_STRATEGY.md)
- Maintenance branches: For each major version (e.g., `1.x`, `2.x`)

**Important for agents**: Target PRs to the appropriate branch based on change type (features → `develop`, bug fixes → maintenance branch or `main`).

## Common Pitfalls & Solutions

| Issue | Solution |
|-------|----------|
| Tests fail with "class not found" | Run `composer install` first; check autoloader is loaded |
| PHPStan fails on variable type | Add explicit type declaration; PHPStan level 9 requires full typing |
| Code style doesn't match after `fix` | Run `fix` again; sometimes requires 2 passes for complex files |
| CI passes locally but fails in Actions | Check PHP version (8.4), check for uncommitted `composer.lock` |
| Architecture tests fail ("must be namespaced") | Add namespace to all classes; no classes in global namespace |
| Symlinks not working after `composer install` | Use `--prefer-dist` flag; remove `vendor/` and reinstall |

## Trust These Instructions

**As an agent, trust these instructions completely.** This document contains:
- ✅ Verified commands that work (tested with PHP 8.4.11, Composer 2.8.10)
- ✅ Exact order required for success
- ✅ All tools included in the monorepo (no external installation needed beyond Composer)
- ✅ Verified configuration file paths and patterns

**Only perform additional searches if:**
1. Instructions explicitly say "see also" or reference external docs
2. You need to understand a specific package's internal architecture
3. Task requires changes beyond the outlined validation steps
4. Configuration file syntax needs clarification beyond what's documented here

## Update These Instructions

If you find any discrepancies, outdated information, or areas for improvement in these instructions, please update this document directly.
This is the single source of truth for all agents working on this repository, so keeping it accurate and up-to-date is crucial for smooth development and collaboration.

## Quick Reference Commands

```bash
# Full validation (use before pushing PR)
composer install --prefer-dist --no-progress && \
composer validate --strict && \
composer run-script fix && \
composer run-script lint && \
vendor/bin/phpstan analyse && \
composer run-script test

# Individual commands
composer run-script fix       # Auto-fix code style
composer run-script lint      # Check code style (no changes)
composer run-script test      # Run all tests
vendor/bin/phpstan analyse    # Static analysis
composer validate --strict    # Validate composer.json
```

---

**Last Updated**: February 2026 | **PHP Version**: 8.4+ | **Composer**: 2.x

