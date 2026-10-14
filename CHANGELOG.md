# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.1] - 2026-10-14

### Added
- Added a project MIT license with attribution for code adapted from Symplify
- Added array formatting regression tests, including nested values, comments, whitespace configuration, and idempotence

### Changed
- Replaced the abandoned `symplify/coding-standard` dependency with local array fixers
- Preserved the `Symplify/*` rule names and legacy class-name aliases

### Fixed
- Array parsing now leaves nested expressions such as `match` arms intact
- Avoided deprecated tokenizer constants while retaining compatibility with PHP-CS-Fixer 3.90

## [1.2.0] - 2026-06-08

### Added
- Integrated Symplify Coding Standard array fixers via custom wrapper fixers under `src/Fixer`
- Added support for setting Symplify fixer rule class names in `setRules()` via internal alias mapping

### Changed
- Added new default array formatting and operator spacing rules, including `trailing_comma_in_multiline`
- Expanded custom fixer registration to include lazy registration of Symplify-based rules
- Adjusted `PedroTroller/line_break_between_method_arguments` `max-length` to `160`
- Added `symplify/coding-standard` runtime dependency
- Expanded CI matrix to run checks on all supported PHP versions (`8.2`, `8.3`, `8.4`, `8.5`) using `.github/workflows/php.yml`

### Fixed
- Added tests to verify Symplify fixers are enabled and class-name rule aliases resolve correctly

## [1.1.2] - 2025-11-22

### Changed
- Updated PHP requirement to `~8.2.0 || ~8.3.0 || ~8.4.0 || ~8.5.0`
- Updated `friendsofphp/php-cs-fixer` to `^3.90`
- Adjusted `pedrotroller/php-cs-custom-fixer` constraint to `^2.34`
- Refreshed CI workflow configuration in `.github/workflows/php.yml`
- Tweaked PHPUnit config `phpunit.xml.dist`
- Minor updates to `.php-cs-fixer.dist.php`

## [1.1.1] - 2025-09-28

### Changed
- Switched base ruleset from `@PER-CS2.0` to `@PER-CS3x0`
- Updated `friendsofphp/php-cs-fixer` from `^3.75` to `^3.88`
- Updated `pedrotroller/php-cs-custom-fixer` from `^2.33` to `^2.35`
- Bumped dev dependencies: `ergebnis/composer-normalize` `^2.48`, `phpstan/phpstan` `^2.1.29`, `phpstan/phpstan-phpunit` `^2.0.7`

## [1.1.0] - 2025-05-31

### Added
- New `setFinder` method to ConfigBuilder for direct Finder instance setting
- Add .gitattributes

### Changed
- Updated PHP requirement from `^8.0 || ^8.1 || ^8.2 || ^8.3` to `^8.1 || ^8.2 || ^8.3 || ^8.4`
- Updated friendsofphp/php-cs-fixer from `^3.52` to `^3.75`
- Updated dev dependencies to newer versions
- Switched from phpstan v1 to v2

## [1.0.1] - 2024-09-29

### Changed
- Updated README.md formatting

## [1.0.0] - 2024-03-29

### Added
- Initial release of the PHP CS Fixer configuration wrapper
- ConfigBuilder class with fluent interface for configuration
- Pre-defined ruleset following PSR-12 and PHP-CS-Fixer standards
- Support for custom fixers from third-party packages
- Methods for specifying directories and files to scan
- Ability to override default rules with custom configurations
