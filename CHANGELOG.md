# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 28/09/2025

### Changed
- Switched base ruleset from `@PER-CS2.0` to `@PER-CS3x0`
- Updated `friendsofphp/php-cs-fixer` from `^3.75` to `^3.88`
- Updated `pedrotroller/php-cs-custom-fixer` from `^2.33` to `^2.35`
- Bumped dev dependencies: `ergebnis/composer-normalize` `^2.48`, `phpstan/phpstan` `^2.1.29`, `phpstan/phpstan-phpunit` `^2.0.7`

## [1.1.0] - 31/05/2025

### Added
- New `setFinder` method to ConfigBuilder for direct Finder instance setting
- Add .gitattributes

### Changed
- Updated PHP requirement from `^8.0 || ^8.1 || ^8.2 || ^8.3` to `^8.1 || ^8.2 || ^8.3 || ^8.4`
- Updated friendsofphp/php-cs-fixer from `^3.52` to `^3.75`
- Updated dev dependencies to newer versions
- Switched from phpstan v1 to v2

## [1.0.1] - 29/09/2024

### Changed
- Updated README.md formatting

## [1.0.0] - 29/03/2024

### Added
- Initial release of the PHP CS Fixer configuration wrapper
- ConfigBuilder class with fluent interface for configuration
- Pre-defined ruleset following PSR-12 and PHP-CS-Fixer standards
- Support for custom fixers from third-party packages
- Methods for specifying directories and files to scan
- Ability to override default rules with custom configurations
