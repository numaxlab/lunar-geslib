# Contributing

Thank you for your interest in contributing to `numaxlab/lunar-geslib`.

## Requirements

- PHP 8.4+
- Composer

## Setup

```bash
git clone https://github.com/numaxlab/lunar-geslib.git
cd lunar-geslib
composer install
```

## Running checks

```bash
# Run all checks (typos, static analysis, rector dry-run, tests)
composer run test

# Individual checks
composer run test:typos      # Spell checking via Peck
composer run test:lint       # Code style check via Pint (dry run)
composer run test:types      # Static analysis via PHPStan / Larastan
composer run test:refactor   # Rector refactor suggestions (dry run)
composer run test:unit       # Unit and feature tests via Pest

# Apply fixes
composer run fix             # Run Rector + Pint
```

## Code standards

- All PHP files must include `declare(strict_types=1)`.
- Follow PSR-12 style enforced by Laravel Pint.
- Pass PHPStan level 8 (Larastan) — no ignored errors without justification.
- All Rector rules must pass in dry-run mode.
- New features require tests. Bug fixes should include a regression test where practical.

## Pull Requests

1. Fork the repository and create a branch from `main`.
2. Ensure all checks pass (`composer run test`).
3. Describe your change clearly in the PR description.
4. Reference any related issues.

## Reporting Issues

Please open an issue on [GitHub](https://github.com/numaxlab/lunar-geslib/issues) with a clear description, steps to reproduce, and the relevant environment details (PHP version, Laravel version, LunarPHP version).
