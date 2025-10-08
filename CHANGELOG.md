# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.4] - 2025-10-08

### Added
- Extension point for custom robots providers via `RobotsProviderInterface`
- Support for multiple robots providers with configurable sort order
- README.md with documentation on how to extend the module

### Changed
- Updated `ApplyRobots` model to support dependency injection of robots providers
- Providers are executed in order of their `getSortOrder()` value

## [1.0.3] - Previous Release

### Features
- Apply custom robots meta tags based on URL patterns and actions
- Support for HTTPS-specific robots settings
- NOINDEX/NOFOLLOW for 404 pages
- Custom robots for paginated pages
