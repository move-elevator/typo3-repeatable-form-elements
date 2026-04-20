# Changelog

## Unreleased

### Breaking

- Dropped TYPO3 v12 support. Minimum version is now TYPO3 v13.4 LTS.

### Added

- TYPO3 v14 compatibility
- PSR-14 `AfterBuildingFinishedEvent` replaces the removed `afterBuildingFinished` SC_OPTIONS hook (Breaking #98239)
- PSR-14 `AfterCurrentPageIsResolvedListener` replaces the removed `afterInitializeCurrentPage` SC_OPTIONS hook
- PSR-14 `BeforeRenderableIsRenderedListener` replaces the removed `beforeRendering` SC_OPTIONS hook
- DDEV multi-version test environment with pre-loaded form fixtures for v13 and v14

### Changed

- Removed `call_user_func` wrapper in `ext_localconf.php`
- Removed deprecated `formEditorPartials` configuration for backend form editor (Deprecation #109306). Both v13 and v14 now use their default composite element rendering.
- Typed `CopyService::$features` property as `Features` instead of `mixed`
- Made `FormHooks` class `final`
- Used explicit nullable types for PHP 8.4 compatibility
- Updated `Services.yaml` to exclude Event classes from DI auto-registration

### Migration

Legacy SC_OPTIONS hook registrations are kept for v13 backward compatibility. The PSR-14 event listeners handle v14 automatically. No manual migration steps required.
