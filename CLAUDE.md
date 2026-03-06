# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

`numaxlab/lunar-geslib` is a Laravel package that integrates [Geslib](https://editorial.trevenque.es/productos/geslib/) (a bookstore management system) with [LunarPHP](https://lunarphp.io/) (a Laravel e-commerce platform). It parses Geslib INTER files and maps their data (books, authors, editorials, topics, etc.) to Lunar models (Products, Brands, Collections, Attributes). It also exposes an API for Geslib to fetch Lunar orders.

The package namespace is `NumaxLab\Lunar\Geslib\`, autoloaded from `src/`.

## Commands

```bash
# Run all checks (typos, static analysis, rector, tests)
composer run test

# Individual checks
composer run test:typos      # peck - spell checking
composer run test:lint       # pint --test (dry run)
composer run test:types      # phpstan
composer run test:refactor   # rector --dry-run
composer run test:unit       # pest --parallel

# Run a single test file
./vendor/bin/pest tests/Unit/InterCommands/ArticleCommandTest.php

# Fix code style and apply rector refactors
composer run fix

# Apply rector only
composer run refactor

# Apply pint only
vendor/bin/pint
```

## Architecture

### Core Data Flow

1. `php artisan lunar:geslib:import` scans for new INTER zip files and dispatches `ProcessGeslibInterFile` jobs to the `geslib-inter-files` queue (must be FIFO, single worker).
2. `ProcessGeslibInterFile` (job) extracts the zip, parses the file in chunks (1000 lines), and dispatches an `InterCommand` for each line type.
3. Each **InterCommand** (in `src/InterCommands/`) is an invokable class that upserts the corresponding Lunar model. After all chunks are processed, batch commands are dispatched via `ProcessGeslibInterFileBatchLine`.
4. **Batch commands** (in `src/InterCommands/Batch/`) handle many-to-many relations (e.g., article-author, article-topic, article-IBIC) that must be applied after all article data is processed.

### InterCommand Pattern

- `AbstractCommand` implements `CommandContract` (invokable). Each command receives a parsed Geslib line object from `numaxlab/geslib-files`.
- Commands set `$isBatch = true` when they represent relations that need deferred processing.
- Geslib line types map to commands in the `match` block inside `ProcessGeslibInterFile::handle()`.

### Model Overrides

The service provider replaces core Lunar models via `ModelManifest`:
- `Lunar\Models\Product` → `NumaxLab\Lunar\Geslib\Models\Product`
- `Lunar\Models\ProductVariant` → `NumaxLab\Lunar\Geslib\Models\ProductVariant`
- `Lunar\Models\Collection` → `NumaxLab\Lunar\Geslib\Models\Collection`
- Adds new: `NumaxLab\Lunar\Geslib\Models\Author` (registered as a new Lunar attribute type)

### Admin Panel

Extended via Filament plugin (`GeslibPlugin`). Extensions to existing Lunar resources use `LunarPanel::extensions()` — see `src/Admin/Filament/Extension/`. Custom resources for `Author` and `GeslibInterFile` are in `src/Admin/Filament/Resources/`.

### External Services

- **Dilve** (`DilveEnricher`): enriches product data after article creation/update (triggered by `GeslibArticleCreated`/`GeslibArticleUpdated` events).
- **CEGAL** (`CegalAvailability`): provides real-time stock availability.
- Both are bound in the service container and enabled via config flags (`GESLIB_DILVE_ENABLED`, `GESLIB_CEGAL_ENABLED`).

### API

When `GESLIB_API_ENABLED=true`, routes from `routes/api.php` are loaded. The `auth.geslib` middleware uses HTTP Basic Auth with credentials from config. The API returns XML responses (via `Response::xml()` macro) for Geslib order synchronization.

### Key Config

All config lives under `config/lunar/geslib.php` (published from `config/geslib.php`), accessed via `config('lunar.geslib.*')`.

### Testing

Tests use Pest with Orchestra Testbench and SQLite in-memory. The base `TestCase` bootstraps the full Lunar + Filament + LunarGeslib service provider stack. Use `$this->asStaff()` for authenticated admin actions in feature tests.

### Code Standards

- All PHP files must have `declare(strict_types=1)`.
- PHP 8.4+ features are used (e.g., property hooks in `AbstractCommand`).
- Rector and Pint enforce style; PHPStan (via Larastan) enforces types.
