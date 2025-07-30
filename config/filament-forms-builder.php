<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the table names used by the package.
    |
    */
    'table_names' => [
        'forms' => 'forms',
        'form_fields' => 'form_fields',
        'form_submissions' => 'form_submissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how forms are accessed publicly.
    |
    */
    'routes' => [
        'prefix' => 'forms',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Submission Storage
    |--------------------------------------------------------------------------
    |
    | Whether to store form submissions in the database.
    |
    */
    'store_submissions' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Field Types
    |--------------------------------------------------------------------------
    |
    | Available field types for form building.
    |
    */
    'field_types' => [
        'text' => 'Text Input',
        'textarea' => 'Textarea',
        'email' => 'Email',
        'select' => 'Select Dropdown',
        'checkbox' => 'Checkbox',
        'radio' => 'Radio Button',
        'date' => 'Date Picker',
        'number' => 'Number Input',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Validation Rules
    |--------------------------------------------------------------------------
    |
    | Common validation rules available in the form builder.
    |
    */
    'validation_rules' => [
        'required' => 'Required',
        'email' => 'Valid Email',
        'numeric' => 'Numeric',
        'min:1' => 'Min Length (1)',
        'min:3' => 'Min Length (3)',
        'min:5' => 'Min Length (5)',
        'max:50' => 'Max Length (50)',
        'max:100' => 'Max Length (100)',
        'max:255' => 'Max Length (255)',
        'max:500' => 'Max Length (500)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Layout Options
    |--------------------------------------------------------------------------
    |
    | Available column span options for form fields.
    |
    */
    'column_spans' => [
        1 => 'Full Width',
        2 => '1/2 Width',
        3 => '1/3 Width',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Form Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for new forms.
    |
    */
    'defaults' => [
        'success_message' => 'Thank you! Your form has been submitted successfully.',
        'submit_button_text' => 'Submit',
        'columns' => 1,
    ],
];