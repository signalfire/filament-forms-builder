<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Unit;

use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class ConfigurationTest extends TestCase
{
    /** @test */
    public function it_has_default_configuration_values()
    {
        $this->assertEquals('forms', config('filament-forms-builder.table_names.forms'));
        $this->assertEquals('form_fields', config('filament-forms-builder.table_names.form_fields'));
        $this->assertEquals('form_submissions', config('filament-forms-builder.table_names.form_submissions'));
    }

    /** @test */
    public function it_has_route_configuration()
    {
        $this->assertEquals('forms', config('filament-forms-builder.routes.prefix'));
        $this->assertEquals(['web'], config('filament-forms-builder.routes.middleware'));
    }

    /** @test */
    public function it_has_field_types_configuration()
    {
        $fieldTypes = config('filament-forms-builder.field_types');
        
        $this->assertIsArray($fieldTypes);
        $this->assertArrayHasKey('text', $fieldTypes);
        $this->assertArrayHasKey('textarea', $fieldTypes);
        $this->assertArrayHasKey('email', $fieldTypes);
        $this->assertArrayHasKey('select', $fieldTypes);
        $this->assertArrayHasKey('checkbox', $fieldTypes);
        $this->assertArrayHasKey('radio', $fieldTypes);
        $this->assertArrayHasKey('date', $fieldTypes);
        $this->assertArrayHasKey('number', $fieldTypes);
    }

    /** @test */
    public function it_has_validation_rules_configuration()
    {
        $validationRules = config('filament-forms-builder.validation_rules');
        
        $this->assertIsArray($validationRules);
        $this->assertArrayHasKey('required', $validationRules);
        $this->assertArrayHasKey('email', $validationRules);
        $this->assertArrayHasKey('numeric', $validationRules);
    }

    /** @test */
    public function it_has_column_spans_configuration()
    {
        $columnSpans = config('filament-forms-builder.column_spans');
        
        $this->assertIsArray($columnSpans);
        $this->assertArrayHasKey(1, $columnSpans);
        $this->assertArrayHasKey(2, $columnSpans);
        $this->assertArrayHasKey(3, $columnSpans);
    }

    /** @test */
    public function it_has_default_form_settings()
    {
        $defaults = config('filament-forms-builder.defaults');
        
        $this->assertArrayHasKey('success_message', $defaults);
        $this->assertArrayHasKey('submit_button_text', $defaults);
        $this->assertArrayHasKey('columns', $defaults);
        
        $this->assertEquals('Thank you! Your form has been submitted successfully.', $defaults['success_message']);
        $this->assertEquals('Submit', $defaults['submit_button_text']);
        $this->assertEquals(1, $defaults['columns']);
    }

    /** @test */
    public function it_can_customize_table_names()
    {
        config([
            'filament-forms-builder.table_names.forms' => 'custom_forms',
            'filament-forms-builder.table_names.form_fields' => 'custom_form_fields',
            'filament-forms-builder.table_names.form_submissions' => 'custom_form_submissions',
        ]);

        $this->assertEquals('custom_forms', config('filament-forms-builder.table_names.forms'));
        $this->assertEquals('custom_form_fields', config('filament-forms-builder.table_names.form_fields'));
        $this->assertEquals('custom_form_submissions', config('filament-forms-builder.table_names.form_submissions'));
    }

    /** @test */
    public function store_submissions_setting_defaults_to_true()
    {
        $this->assertTrue(config('filament-forms-builder.store_submissions'));
    }
}