# Changelog

All notable changes to `numaxlab/lunar-geslib` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0-beta.5] - 2026-04-25

### Fixed

- Null safety in `ProductIndexer`: guard `authors`, `productType`, and `created_at` against null values to prevent crashes on incomplete product data.
- Log merging in `ProcessGeslibInterFileBatchLine`: use `array_merge` instead of nested push so command logs are flattened correctly into the parent log array.

### Changed

- Replaced `@dev` / `dev-main` constraints for `atomic-laravel`, `cegal-client`, and `dilve-client` with explicit `^1.0@beta` constraints matching their released beta versions.
- Pinned dev tools (`laravel/pint`, `peckphp/peck`, `rector/rector`) to stable constraint ranges instead of `dev-main`.

[Unreleased]: https://github.com/numaxlab/lunar-geslib/compare/1.0.0-beta.5...HEAD
[1.0.0-beta.5]: https://github.com/numaxlab/lunar-geslib/releases/tag/1.0.0-beta.5
