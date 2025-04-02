# Changelog

All notable changes to the LaraTrans package will be documented in this file.

## [3.0.0] - 2025-04-02
### Added
- Multiple storage strategies (single table and dedicated tables)
- Migration command to switch between storage strategies
- Dedicated tables creation command
- Cleanup command for removing old translation data
- Property-specific required locales validation
- Unique translations validation
- Model strategy accessor methods
- Extended translation methods
- Error handling and reporting

### Changed
- Complete architecture overhaul using Strategy pattern
- Enhanced validation system
- Improved error messages
- Bulk translations handling
- More efficient database queries

### Fixed
- Long index name issue in migrations
- Validation for existing translations
- Null values in translations

## [2.0.0] - Previous Release
### Added
- Laravel 11 support
- Additional validation system improvements
- Expanded configuration options
- Automatic locale fallback support

### Changed
- Improved performance and reliability

## [1.0.4] - Initial Release
- Basic translation functionality