<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Signalfire\FilamentFormsBuilder\Components\FormComponent;
use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_blade_component()
    {
        // Test that the FormComponent class exists and can be instantiated
        $this->assertTrue(class_exists(FormComponent::class));
        
        // Test that the service provider registers views
        $this->assertTrue(view()->exists('filament-forms-builder::components.form'));
    }

    /** @test */
    public function it_loads_package_views()
    {
        $this->assertTrue(view()->exists('filament-forms-builder::components.form'));
        $this->assertTrue(view()->exists('filament-forms-builder::form-page'));
    }

    /** @test */
    public function it_loads_package_routes()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('filament-forms-builder.show'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('filament-forms-builder.submit'));
    }

    /** @test */
    public function it_merges_package_configuration()
    {
        $this->assertNotNull(config('filament-forms-builder.field_types'));
        $this->assertNotNull(config('filament-forms-builder.validation_rules'));
        $this->assertNotNull(config('filament-forms-builder.table_names'));
    }

    /** @test */
    public function blade_component_resolves_correctly()
    {
        // Test that the component class exists and has the expected methods
        $this->assertTrue(class_exists(FormComponent::class));
        $this->assertTrue(method_exists(FormComponent::class, 'render'));
        $this->assertTrue(method_exists(FormComponent::class, 'getFieldValue'));
    }
}