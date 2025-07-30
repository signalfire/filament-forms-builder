<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Feature;

use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormField;
use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class FormValidationTest extends TestCase
{
    /** @test */
    public function it_validates_text_fields_with_min_length()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->withValidation('min:5')->create([
            'form_id' => $form->id,
            'key' => 'name',
            'type' => 'text',
        ]);

        $response = $this->post("/forms/{$form->slug}", [
            'name' => 'abc', // Too short
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_validates_text_fields_with_max_length()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->withValidation('max:10')->create([
            'form_id' => $form->id,
            'key' => 'name',
            'type' => 'text',
        ]);

        $response = $this->post("/forms/{$form->slug}", [
            'name' => 'This is way too long for the validation rule',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_validates_email_fields()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'type' => 'email',
            'validation_rules' => 'email',
        ]);

        // Invalid email
        $response1 = $this->post("/forms/{$form->slug}", [
            'email' => 'invalid-email',
        ]);
        $response1->assertSessionHasErrors(['email']);

        // Valid email
        $response2 = $this->post("/forms/{$form->slug}", [
            'email' => 'valid@example.com',
        ]);
        $response2->assertSessionDoesntHaveErrors(['email']);
    }

    /** @test */
    public function it_validates_numeric_fields()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'age',
            'type' => 'number',
            'validation_rules' => 'numeric',
        ]);

        // Non-numeric input
        $response1 = $this->post("/forms/{$form->slug}", [
            'age' => 'not-a-number',
        ]);
        $response1->assertSessionHasErrors(['age']);

        // Valid numeric input
        $response2 = $this->post("/forms/{$form->slug}", [
            'age' => '25',
        ]);
        $response2->assertSessionDoesntHaveErrors(['age']);
    }

    /** @test */
    public function it_validates_date_fields()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'birth_date',
            'type' => 'date',
            'validation_rules' => 'date',
        ]);

        // Invalid date
        $response1 = $this->post("/forms/{$form->slug}", [
            'birth_date' => 'not-a-date',
        ]);
        $response1->assertSessionHasErrors(['birth_date']);

        // Valid date
        $response2 = $this->post("/forms/{$form->slug}", [
            'birth_date' => '1990-01-01',
        ]);
        $response2->assertSessionDoesntHaveErrors(['birth_date']);
    }

    /** @test */
    public function it_validates_multiple_rules_on_single_field()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->withValidation('required|email|max:50')->create([
            'form_id' => $form->id,
            'key' => 'contact_email',
            'type' => 'email',
        ]);

        // Test required validation
        $response1 = $this->post("/forms/{$form->slug}", []);
        $response1->assertSessionHasErrors(['contact_email']);

        // Test email validation
        $response2 = $this->post("/forms/{$form->slug}", [
            'contact_email' => 'invalid-email',
        ]);
        $response2->assertSessionHasErrors(['contact_email']);

        // Test max length validation
        $response3 = $this->post("/forms/{$form->slug}", [
            'contact_email' => 'this-is-a-very-long-email-address-that-exceeds-the-max-length@example.com',
        ]);
        $response3->assertSessionHasErrors(['contact_email']);

        // Valid input
        $response4 = $this->post("/forms/{$form->slug}", [
            'contact_email' => 'valid@example.com',
        ]);
        $response4->assertSessionDoesntHaveErrors(['contact_email']);
    }

    /** @test */
    public function it_automatically_adds_required_rule_for_required_fields()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'type' => 'text',
            'validation_rules' => 'min:3', // No 'required' rule specified
            'is_required' => true,
        ]);

        $response = $this->post("/forms/{$form->slug}", []);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_doesnt_duplicate_required_rule()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'type' => 'text',
            'validation_rules' => 'required|min:3', // 'required' already specified
            'is_required' => true,
        ]);

        // Test that the validation rules are correctly processed
        $rules = $field->getValidationRulesArray();
        $this->assertEquals(['required', 'min:3'], $rules);
        $this->assertCount(2, $rules); // Should not have duplicate 'required'
    }

    /** @test */
    public function it_validates_select_field_options()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'country',
            'type' => 'select',
            'validation_rules' => 'in:us,ca,mx',
            'options' => [
                ['value' => 'us', 'label' => 'United States'],
                ['value' => 'ca', 'label' => 'Canada'],
                ['value' => 'mx', 'label' => 'Mexico'],
            ],
        ]);

        // Invalid option
        $response1 = $this->post("/forms/{$form->slug}", [
            'country' => 'invalid-country',
        ]);
        $response1->assertSessionHasErrors(['country']);

        // Valid option
        $response2 = $this->post("/forms/{$form->slug}", [
            'country' => 'us',
        ]);
        $response2->assertSessionDoesntHaveErrors(['country']);
    }

    /** @test */
    public function it_validates_checkbox_arrays()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'interests',
            'type' => 'checkbox',
            'validation_rules' => 'array',
        ]);

        // Valid checkbox array
        $response1 = $this->post("/forms/{$form->slug}", [
            'interests' => ['newsletter', 'updates'],
        ]);
        $response1->assertSessionDoesntHaveErrors(['interests']);

        // Single checkbox value (also valid)
        $response2 = $this->post("/forms/{$form->slug}", [
            'interests' => ['newsletter'],
        ]);
        $response2->assertSessionDoesntHaveErrors(['interests']);
    }

    /** @test */
    public function it_validates_textarea_fields()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->withValidation('min:10|max:500')->create([
            'form_id' => $form->id,
            'key' => 'message',
            'type' => 'textarea',
        ]);

        // Too short
        $response1 = $this->post("/forms/{$form->slug}", [
            'message' => 'Short',
        ]);
        $response1->assertSessionHasErrors(['message']);

        // Too long
        $response2 = $this->post("/forms/{$form->slug}", [
            'message' => str_repeat('a', 501),
        ]);
        $response2->assertSessionHasErrors(['message']);

        // Just right
        $response3 = $this->post("/forms/{$form->slug}", [
            'message' => 'This is a valid message that meets the length requirements.',
        ]);
        $response3->assertSessionDoesntHaveErrors(['message']);
    }

    /** @test */
    public function it_handles_empty_validation_rules()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'optional_field',
            'type' => 'text',
            'validation_rules' => '', // Empty rules
            'is_required' => false,
        ]);

        $response = $this->post("/forms/{$form->slug}", [
            'optional_field' => 'Any value should be fine',
        ]);

        $response->assertSessionDoesntHaveErrors(['optional_field']);
    }

    /** @test */
    public function it_validates_radio_button_selections()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'choice',
            'type' => 'radio',
            'validation_rules' => 'in:yes,no,maybe',
            'options' => [
                ['value' => 'yes', 'label' => 'Yes'],
                ['value' => 'no', 'label' => 'No'],
                ['value' => 'maybe', 'label' => 'Maybe'],
            ],
        ]);

        // Invalid radio selection
        $response1 = $this->post("/forms/{$form->slug}", [
            'choice' => 'invalid-choice',
        ]);
        $response1->assertSessionHasErrors(['choice']);

        // Valid radio selection
        $response2 = $this->post("/forms/{$form->slug}", [
            'choice' => 'yes',
        ]);
        $response2->assertSessionDoesntHaveErrors(['choice']);
    }

    /** @test */
    public function it_uses_custom_field_labels_in_validation_messages()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        FormField::factory()->required()->create([
            'form_id' => $form->id,
            'key' => 'user_email',
            'label' => 'Your Email Address',
            'type' => 'email',
        ]);

        $response = $this->post("/forms/{$form->slug}", []);

        $response->assertSessionHasErrors(['user_email']);
        
        $errors = session('errors');
        $errorMessage = $errors->first('user_email');
        $this->assertStringContainsString('Your Email Address', $errorMessage);
    }

    /** @test */
    public function it_validates_different_field_types_together()
    {
        $form = Form::factory()->create(['slug' => 'complex-form']);
        
        // Required text field
        FormField::factory()->required()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'type' => 'text',
            'label' => 'Full Name',
        ]);

        // Email field with validation
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'type' => 'email',
            'validation_rules' => 'required|email',
            'label' => 'Email',
        ]);

        // Numeric field
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'age',
            'type' => 'number',
            'validation_rules' => 'numeric|min:18|max:120',
            'label' => 'Age',
        ]);

        // Test all validations together
        $response = $this->post("/forms/{$form->slug}", [
            'name' => '', // Invalid: required
            'email' => 'invalid-email', // Invalid: not proper email
            'age' => '15', // Invalid: under minimum
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'age']);

        // Test valid submission
        $response2 = $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => '25',
        ]);

        $response2->assertSessionDoesntHaveErrors(['name', 'email', 'age']);
    }
}