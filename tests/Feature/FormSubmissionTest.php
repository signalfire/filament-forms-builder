<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Signalfire\FilamentFormsBuilder\Events\FormSubmitted;
use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormField;
use Signalfire\FilamentFormsBuilder\Models\FormSubmission;
use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    /** @test */
    public function it_can_submit_a_valid_form()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'type' => 'text',
            'is_required' => false,
        ]);

        FormField::factory()->email()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'is_required' => false,
        ]);

        $response = $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', $form->success_message);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->required()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Full Name',
        ]);

        $response = $this->post("/forms/{$form->slug}", []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_validates_email_fields()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'type' => 'email',
            'validation_rules' => 'email',
        ]);

        $response = $this->post("/forms/{$form->slug}", [
            'email' => 'invalid-email',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_custom_validation_rules()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->withValidation('min:5|max:10')->create([
            'form_id' => $form->id,
            'key' => 'username',
        ]);

        // Test min validation
        $response1 = $this->post("/forms/{$form->slug}", [
            'username' => 'abc', // too short
        ]);

        $response1->assertSessionHasErrors(['username']);

        // Test max validation
        $response2 = $this->post("/forms/{$form->slug}", [
            'username' => 'this-is-way-too-long', // too long
        ]);

        $response2->assertSessionHasErrors(['username']);

        // Test valid input
        $response3 = $this->post("/forms/{$form->slug}", [
            'username' => 'goodname', // just right
        ]);

        $response3->assertSessionDoesntHaveErrors(['username']);
    }

    /** @test */
    public function it_stores_submission_in_database_when_enabled()
    {
        config(['filament-forms-builder.store_submissions' => true]);

        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        $this->assertDatabaseCount('form_submissions', 0);

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        $this->assertDatabaseCount('form_submissions', 1);
        
        $submission = FormSubmission::first();
        $this->assertEquals($form->id, $submission->form_id);
        $this->assertEquals(['name' => 'John Doe'], $submission->data);
        $this->assertNotNull($submission->ip_address);
        $this->assertNotNull($submission->user_agent);
    }

    /** @test */
    public function it_does_not_store_submission_when_disabled()
    {
        config(['filament-forms-builder.store_submissions' => false]);

        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        $this->assertDatabaseCount('form_submissions', 0);
    }

    /** @test */
    public function it_dispatches_form_submitted_event()
    {
        Event::fake();

        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        Event::assertDispatched(FormSubmitted::class, function ($event) use ($form) {
            return $event->form->id === $form->id
                && $event->data === ['name' => 'John Doe'];
        });
    }

    /** @test */
    public function it_returns_404_for_inactive_form_submission()
    {
        $form = Form::factory()->inactive()->create(['slug' => 'contact-us']);

        $response = $this->post("/forms/{$form->slug}", []);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_non_existent_form_submission()
    {
        $response = $this->post('/forms/non-existent', []);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_handles_checkbox_arrays()
    {
        $form = Form::factory()->create(['slug' => 'survey']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'interests',
            'type' => 'checkbox',
            'options' => [
                ['value' => 'newsletter', 'label' => 'Newsletter'],
                ['value' => 'updates', 'label' => 'Updates'],
            ],
        ]);

        $this->post("/forms/{$form->slug}", [
            'interests' => ['newsletter', 'updates'],
        ]);

        $submission = FormSubmission::first();
        $this->assertEquals(['interests' => ['newsletter', 'updates']], $submission->data);
    }

    /** @test */
    public function it_handles_single_checkbox()
    {
        $form = Form::factory()->create(['slug' => 'survey']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'agree',
            'type' => 'checkbox',
        ]);

        $this->post("/forms/{$form->slug}", [
            'agree' => '1',
        ]);

        $submission = FormSubmission::first();
        $this->assertEquals(['agree' => '1'], $submission->data);
    }

    /** @test */
    public function it_preserves_input_on_validation_errors()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->required()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
        ]);

        $response = $this->post("/forms/{$form->slug}", [
            'email' => 'john@example.com', // valid
            // name is missing - invalid
        ]);

        $response->assertRedirect();
        $response->assertSessionHasInput('email', 'john@example.com');
        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_uses_custom_field_labels_in_validation_messages()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->required()->create([
            'form_id' => $form->id,
            'key' => 'full_name',
            'label' => 'Your Full Name',
        ]);

        $response = $this->post("/forms/{$form->slug}", []);

        $response->assertSessionHasErrors(['full_name']);
        
        // The validation message should use the custom label
        $errors = session('errors');
        $this->assertStringContainsString('Your Full Name', $errors->first('full_name'));
    }

    /** @test */
    public function it_ignores_hidden_fields_in_validation()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->hidden()->required()->create([
            'form_id' => $form->id,
            'key' => 'hidden_field',
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'visible_field',
        ]);

        $response = $this->post("/forms/{$form->slug}", [
            'visible_field' => 'test',
            // hidden_field is not provided, but should be ignored
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionDoesntHaveErrors(['hidden_field']);
    }
}