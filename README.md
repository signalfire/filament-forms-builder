# Filament Forms Builder

A FilamentPHP plugin that allows users to create and manage custom forms from the admin panel with a drag-and-drop interface.

## Features

- **Form Management**: Create multiple forms with unique slugs and custom settings
- **Field Builder**: Add various field types (text, textarea, select, checkbox, radio, date, email, number)
- **Drag & Drop**: Reorder form fields with drag-and-drop functionality
- **Column Layout**: Support for 1-3 column layouts with customizable field spans
- **Validation**: Built-in validation rules with custom error messages
- **Public Forms**: Generate public forms accessible via custom URLs
- **Submission Handling**: Store submissions in database or process via events
- **Extensible**: Event-driven architecture for custom processing

## Installation

Install the package via composer:

```bash
composer require signalfire/filament-forms-builder
```

Register the plugin in your Filament panel provider:

```php
use Signalfire\FilamentFormsBuilder\FilamentFormsBuilderPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentFormsBuilderPlugin::make(),
        ]);
}
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="filament-forms-builder-config"
php artisan migrate
```

## Usage

### Creating Forms

1. Navigate to the Forms section in your Filament admin panel
2. Create a new form with a name, slug, and description
3. Add fields using the Fields relation manager
4. Configure field types, validation, and layout options
5. Use drag-and-drop to reorder fields

### Displaying Forms

Use the Blade component to display forms in your frontend:

```blade
<x-filament-forms-builder::form slug="contact-us" />
```

Or access forms directly via URL:
```
/forms/contact-us
```

### Processing Submissions

Listen for the `FormSubmitted` event to process form data:

```php
use Signalfire\FilamentFormsBuilder\Events\FormSubmitted;

Event::listen(FormSubmitted::class, function (FormSubmitted $event) {
    // Send email, call webhook, etc.
    Mail::to('admin@example.com')->send(new FormSubmissionMail($event->data));
});
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-forms-builder-config"
```

## Field Types

Supported field types:
- Text Input
- Textarea
- Email
- Select Dropdown
- Radio Buttons
- Checkboxes
- Date Picker
- Number Input

## Requirements

- PHP 8.1+
- Laravel 10.0+
- Filament 3.0+

## License

MIT