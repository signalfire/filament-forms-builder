<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Unit;

use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormField;
use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class FormFieldModelTest extends TestCase
{
    /** @test */
    public function it_can_create_a_form_field()
    {
        $form = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'label' => 'Email Address',
            'type' => 'email',
        ]);

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('email', $field->key);
        $this->assertEquals('Email Address', $field->label);
        $this->assertEquals('email', $field->type);
        $this->assertEquals($form->id, $field->form_id);
    }

    /** @test */
    public function it_belongs_to_a_form()
    {
        $form = Form::factory()->create();
        $field = FormField::factory()->create(['form_id' => $form->id]);

        $this->assertInstanceOf(Form::class, $field->form);
        $this->assertEquals($form->id, $field->form->id);
    }

    /** @test */
    public function it_can_get_validation_rules_array()
    {
        $field = FormField::factory()->create([
            'validation_rules' => 'required|email|max:255',
            'is_required' => false,
        ]);

        $rules = $field->getValidationRulesArray();
        
        $this->assertEquals(['required', 'email', 'max:255'], $rules);
    }

    /** @test */
    public function it_adds_required_rule_when_field_is_required()
    {
        $field = FormField::factory()->required()->create([
            'validation_rules' => 'email|max:255',
        ]);

        $rules = $field->getValidationRulesArray();
        
        $this->assertContains('required', $rules);
        $this->assertEquals(['required', 'email', 'max:255'], $rules);
    }

    /** @test */
    public function it_doesnt_duplicate_required_rule()
    {
        $field = FormField::factory()->required()->create([
            'validation_rules' => 'required|email|max:255',
        ]);

        $rules = $field->getValidationRulesArray();
        
        $this->assertEquals(['required', 'email', 'max:255'], $rules);
    }

    /** @test */
    public function it_returns_field_type_label()
    {
        $field = FormField::factory()->create(['type' => 'email']);
        
        $this->assertEquals('Email', $field->getFieldTypeLabel());
    }

    /** @test */
    public function it_returns_options_for_select_fields()
    {
        $field = FormField::factory()->select()->create();

        $options = $field->getOptionsForSelect();
        
        $this->assertIsArray($options);
        $this->assertEquals([
            'option1' => 'Option 1',
            'option2' => 'Option 2',
            'option3' => 'Option 3',
        ], $options);
    }

    /** @test */
    public function it_returns_options_for_radio_fields()
    {
        $field = FormField::factory()->radio()->create();

        $options = $field->getOptionsForSelect();
        
        $this->assertEquals([
            'yes' => 'Yes',
            'no' => 'No',
        ], $options);
    }

    /** @test */
    public function it_returns_empty_array_for_non_option_fields()
    {
        $field = FormField::factory()->create(['type' => 'text']);

        $options = $field->getOptionsForSelect();
        
        $this->assertEquals([], $options);
    }

    /** @test */
    public function it_can_scope_visible_fields()
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id, 'is_visible' => true]);
        FormField::factory()->hidden()->create(['form_id' => $form->id]);

        $visibleFields = FormField::visible()->get();
        
        $this->assertCount(1, $visibleFields);
        $this->assertTrue($visibleFields->first()->is_visible);
    }

    /** @test */
    public function it_can_scope_ordered_fields()
    {
        $form = Form::factory()->create();
        $field1 = FormField::factory()->sortOrder(3)->create(['form_id' => $form->id]);
        $field2 = FormField::factory()->sortOrder(1)->create(['form_id' => $form->id]);
        $field3 = FormField::factory()->sortOrder(2)->create(['form_id' => $form->id]);

        $orderedFields = FormField::ordered()->get();
        
        $this->assertEquals($field2->id, $orderedFields[0]->id);
        $this->assertEquals($field3->id, $orderedFields[1]->id);
        $this->assertEquals($field1->id, $orderedFields[2]->id);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $field = FormField::factory()->create([
            'options' => [['value' => 'test', 'label' => 'Test']],
            'conditional_logic' => ['field' => 'other', 'value' => 'yes'],
            'is_required' => '1',
            'is_visible' => '0',
            'column_span' => '2',
            'sort_order' => '5',
        ]);

        $this->assertIsArray($field->options);
        $this->assertIsArray($field->conditional_logic);
        $this->assertIsBool($field->is_required);
        $this->assertIsBool($field->is_visible);
        $this->assertIsInt($field->column_span);
        $this->assertIsInt($field->sort_order);
    }

    /** @test */
    public function it_handles_string_options_correctly()
    {
        $field = FormField::factory()->create([
            'type' => 'select',
            'options' => ['option1', 'option2', 'option3'],
        ]);

        $options = $field->getOptionsForSelect();
        
        $this->assertEquals([
            'option1' => 'option1',
            'option2' => 'option2',
            'option3' => 'option3',
        ], $options);
    }
}