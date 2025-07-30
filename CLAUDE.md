# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a FilamentPHP plugin called "FilamentFormsBuilder" that allows users to create and manage custom contact forms from the admin panel. It's a complete Laravel package with Filament integration.

## Key Architecture

**Models:**
- `Form` - Main form entity with name, slug, settings
- `FormField` - Individual form fields with type, validation, ordering
- `FormSubmission` - Stores form submission data

**Core Components:**
- `FormResource` - Filament resource for managing forms
- `FieldsRelationManager` - Drag & drop field management with reordering via `reorderable('sort_order')`
- `SubmissionsRelationManager` - View and manage form submissions
- `FormComponent` - Blade component for public form rendering
- `FormController` - Handles public form display and submission

**Form Field Types:**
- text, textarea, email, select, checkbox, radio, date, number
- Each field supports validation rules, column spans, help text, options

## Development Commands

This is a Laravel package, so testing/development requires a host Laravel application:

**Package Development:**
- `composer install` - Install PHP dependencies
- Use within a Laravel app for testing
- Migrations are in `database/migrations/`
- Views are in `resources/views/`

**Integration Testing:**
- Install in a Laravel app with Filament
- Register the plugin: `FilamentFormsBuilderPlugin::make()`
- Run migrations: `php artisan migrate`
- Publish config: `php artisan vendor:publish --tag="filament-forms-builder-config"`

## Key Implementation Details

**Drag & Drop Field Ordering:**
- Uses Filament's `reorderable('sort_order')` in FieldsRelationManager
- Fields are ordered by `sort_order` column automatically

**Form Rendering:**
- Public forms accessible at `/forms/{slug}`
- Blade component: `<x-filament-forms-builder::form slug="contact-us" />`
- Supports 1-3 column layouts with responsive field spans

**Validation:**
- Server-side validation using Laravel's validator
- Configurable validation rules per field
- Required fields automatically get 'required' rule

**Extensibility:**
- `FormSubmitted` event dispatched after successful submission
- Configuration file for customizing field types, validation rules
- Optional submission storage (can be disabled)

**Column Layout System:**
- Forms support 1-3 columns
- Fields have `column_span` property for width control
- Responsive CSS classes handle mobile/desktop layouts

## Configuration

Key config options in `config/filament-forms-builder.php`:
- `store_submissions` - Whether to save submissions to database
- `field_types` - Available field types
- `validation_rules` - Available validation rules
- `routes.prefix` - URL prefix for public forms (default: 'forms')

## Testing

The package includes comprehensive tests covering:

**Unit Tests:**
- Model functionality and relationships
- Configuration handling
- Field validation and formatting

**Feature Tests:**
- Form display and rendering
- Form submission with validation
- Filament resource integration
- Event dispatching
- Blade component functionality

**Test Commands:**
- `composer test` - Run full test suite
- `composer test-coverage` - Run tests with HTML coverage report
- `vendor/bin/phpunit --filter ModelTest` - Run specific test classes
- `vendor/bin/phpunit tests/Unit` - Run only unit tests
- `vendor/bin/phpunit tests/Feature` - Run only feature tests

**Test Structure:**
```
tests/
├── TestCase.php (Base test case with Laravel setup)
├── Unit/ (Model and class tests)
│   ├── FormModelTest.php
│   ├── FormFieldModelTest.php
│   ├── FormSubmissionModelTest.php
│   └── ConfigurationTest.php
└── Feature/ (Integration tests)
    ├── FormDisplayTest.php
    ├── FormSubmissionTest.php
    ├── FormValidationTest.php
    ├── FormEventsTest.php
    ├── FormComponentTest.php
    ├── FilamentResourceTest.php
    └── ServiceProviderTest.php
```

**Database Factories:**
All models include factories for easy test data generation:
- `Form::factory()` - Create test forms
- `FormField::factory()` - Create test form fields with various types
- `FormSubmission::factory()` - Create test submissions

**CI/CD:**
GitHub Actions workflow included for automated testing across:
- PHP 8.1, 8.2, 8.3
- Laravel 10.x, 11.x