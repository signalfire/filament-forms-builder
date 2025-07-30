<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Feature;

use Signalfire\FilamentFormsBuilder\Components\FormComponent;
use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormField;
use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class FormComponentTest extends TestCase
{
    /** @test */
    public function it_can_instantiate_with_valid_form_slug()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);

        $component = new FormComponent('contact-us');

        $this->assertInstanceOf(FormComponent::class, $component);
        $this->assertEquals($form->id, $component->form->id);
        $this->assertEquals('contact-us', $component->slug);
    }

    /** @test */
    public function it_throws_exception_for_invalid_slug()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        new FormComponent('non-existent-form');
    }

    /** @test */
    public function it_throws_exception_for_inactive_form()
    {
        Form::factory()->inactive()->create(['slug' => 'inactive-form']);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        new FormComponent('inactive-form');
    }

    /** @test */
    public function it_loads_only_visible_fields()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'is_visible' => true,
        ]);

        FormField::factory()->hidden()->create([
            'form_id' => $form->id,
        ]);

        $component = new FormComponent('test-form');

        $this->assertCount(1, $component->form->fields);
        $this->assertTrue($component->form->fields->first()->is_visible);
    }

    /** @test */
    public function it_orders_fields_by_sort_order()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        
        $field1 = FormField::factory()->sortOrder(2)->create(['form_id' => $form->id]);
        $field2 = FormField::factory()->sortOrder(1)->create(['form_id' => $form->id]);
        $field3 = FormField::factory()->sortOrder(3)->create(['form_id' => $form->id]);

        $component = new FormComponent('test-form');

        $fields = $component->form->fields;
        $this->assertEquals($field2->id, $fields[0]->id);
        $this->assertEquals($field1->id, $fields[1]->id);
        $this->assertEquals($field3->id, $fields[2]->id);
    }

    /** @test */
    public function it_can_get_field_value_from_old_input()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);

        $component = new FormComponent('test-form', [], ['name' => 'John Doe']);

        $this->assertEquals('John Doe', $component->getFieldValue('name'));
        $this->assertNull($component->getFieldValue('email'));
    }

    /** @test */
    public function it_can_check_for_field_errors()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);

        $component = new FormComponent('test-form', [
            'name' => ['The name field is required.'],
            'email' => ['The email field must be a valid email address.'],
        ]);

        $this->assertTrue($component->hasError('name'));
        $this->assertTrue($component->hasError('email'));
        $this->assertFalse($component->hasError('phone'));
    }

    /** @test */
    public function it_can_get_first_error_message()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);

        $component = new FormComponent('test-form', [
            'name' => ['The name field is required.', 'The name field must be at least 3 characters.'],
        ]);

        $this->assertEquals('The name field is required.', $component->getError('name'));
        $this->assertNull($component->getError('email'));
    }

    /** @test */
    public function it_returns_correct_column_class_for_single_column()
    {
        $form = Form::factory()->create(['slug' => 'test-form', 'columns' => 1]);

        $component = new FormComponent('test-form');

        $this->assertEquals('grid-cols-1', $component->getColumnClass());
    }

    /** @test */
    public function it_returns_correct_column_class_for_two_columns()
    {
        $form = Form::factory()->multiColumn(2)->create(['slug' => 'test-form']);

        $component = new FormComponent('test-form');

        $this->assertEquals('grid-cols-1 md:grid-cols-2', $component->getColumnClass());
    }

    /** @test */
    public function it_returns_correct_column_class_for_three_columns()
    {
        $form = Form::factory()->multiColumn(3)->create(['slug' => 'test-form']);

        $component = new FormComponent('test-form');

        $this->assertEquals('grid-cols-1 md:grid-cols-2 lg:grid-cols-3', $component->getColumnClass());
    }

    /** @test */
    public function it_returns_correct_field_column_span_for_single_column_form()
    {
        $form = Form::factory()->create(['slug' => 'test-form', 'columns' => 1]);

        $component = new FormComponent('test-form');

        $this->assertEquals('col-span-1', $component->getFieldColumnSpan(1));
        $this->assertEquals('col-span-1', $component->getFieldColumnSpan(2));
        $this->assertEquals('col-span-1', $component->getFieldColumnSpan(3));
    }

    /** @test */
    public function it_returns_correct_field_column_span_for_multi_column_form()
    {
        $form = Form::factory()->multiColumn(2)->create(['slug' => 'test-form']);

        $component = new FormComponent('test-form');

        $this->assertEquals('col-span-full', $component->getFieldColumnSpan(1)); // Full width
        $this->assertEquals('col-span-1 md:col-span-1', $component->getFieldColumnSpan(2)); // Half width
    }

    /** @test */
    public function it_can_render_blade_view()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);

        $component = new FormComponent('test-form');

        $view = $component->render();

        $this->assertEquals('filament-forms-builder::components.form', $view->name());
    }

    /** @test */
    public function it_renders_with_form_data_in_blade_component()
    {
        $form = Form::factory()->create([
            'slug' => 'test-form',
            'name' => 'Test Form',
            'description' => 'This is a test form',
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Your Name',
            'type' => 'text',
        ]);

        $component = new \Signalfire\FilamentFormsBuilder\Components\FormComponent(
            slug: 'test-form',
            errors: [],
            old: []
        );

        $rendered = $component->resolveView()->render();

        $this->assertStringContainsString('Test Form', $rendered);
        $this->assertStringContainsString('This is a test form', $rendered);
        $this->assertStringContainsString('Your Name', $rendered);
    }
}