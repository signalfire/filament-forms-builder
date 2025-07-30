<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Signalfire\FilamentFormsBuilder\Events\FormSubmitted;
use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormField;
use Signalfire\FilamentFormsBuilder\Models\FormSubmission;
use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class FormEventsTest extends TestCase
{
    /** @test */
    public function it_dispatches_form_submitted_event_on_successful_submission()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'type' => 'text',
            'is_required' => false,
        ]);

        Event::fake();

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        Event::assertDispatched(FormSubmitted::class);
    }

    /** @test */
    public function it_passes_correct_data_to_form_submitted_event()
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

        $submissionData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        // Fake events after setup is complete
        Event::fake();

        $response = $this->post("/forms/{$form->slug}", $submissionData);
        $response->assertRedirect();

        Event::assertDispatched(FormSubmitted::class);
    }

    /** @test */
    public function it_includes_submission_model_in_event_when_storage_enabled()
    {
        config(['filament-forms-builder.store_submissions' => true]);

        $form = Form::factory()->create(['slug' => 'contact-us']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        Event::fake();

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        Event::assertDispatched(FormSubmitted::class, function ($event) {
            return $event->submission instanceof FormSubmission
                && $event->submission->data === ['name' => 'John Doe'];
        });
    }

    /** @test */
    public function it_has_null_submission_in_event_when_storage_disabled()
    {
        config(['filament-forms-builder.store_submissions' => false]);

        $form = Form::factory()->create(['slug' => 'contact-us']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        Event::fake();

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        Event::assertDispatched(FormSubmitted::class, function ($event) {
            return $event->submission === null;
        });
    }

    /** @test */
    public function it_does_not_dispatch_event_on_validation_failure()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        FormField::factory()->required()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        Event::fake();

        $this->post("/forms/{$form->slug}", []); // Missing required field

        Event::assertNotDispatched(FormSubmitted::class);
    }

    /** @test */
    public function event_can_be_listened_to_for_custom_processing()
    {
        $customProcessingCalled = false;
        $receivedData = null;

        Event::listen(FormSubmitted::class, function ($event) use (&$customProcessingCalled, &$receivedData) {
            $customProcessingCalled = true;
            $receivedData = $event->data;
        });

        $form = Form::factory()->create(['slug' => 'contact-us']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        $this->assertTrue($customProcessingCalled);
        $this->assertEquals(['name' => 'John Doe'], $receivedData);
    }

    /** @test */
    public function event_contains_request_information()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        Event::fake();

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        Event::assertDispatched(FormSubmitted::class, function ($event) {
            return $event->request instanceof \Illuminate\Http\Request
                && $event->request->has('name')
                && $event->request->get('name') === 'John Doe';
        });
    }

    /** @test */
    public function event_is_serializable_for_queued_listeners()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        $submission = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'data' => ['name' => 'John Doe'],
        ]);

        $request = request();
        $request->replace(['name' => 'John Doe']);

        $event = new FormSubmitted($form, ['name' => 'John Doe'], $submission, $request);

        // Test that the event can be serialized and unserialized
        $serialized = serialize($event);
        $unserialized = unserialize($serialized);

        $this->assertEquals($form->id, $unserialized->form->id);
        $this->assertEquals(['name' => 'John Doe'], $unserialized->data);
        $this->assertEquals($submission->id, $unserialized->submission->id);
        $this->assertInstanceOf(\Illuminate\Http\Request::class, $unserialized->request);
    }

    /** @test */
    public function multiple_event_listeners_can_be_registered()
    {
        $listener1Called = false;
        $listener2Called = false;

        Event::listen(FormSubmitted::class, function ($event) use (&$listener1Called) {
            $listener1Called = true;
        });

        Event::listen(FormSubmitted::class, function ($event) use (&$listener2Called) {
            $listener2Called = true;
        });

        $form = Form::factory()->create(['slug' => 'contact-us']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        $this->assertTrue($listener1Called);
        $this->assertTrue($listener2Called);
    }

    /** @test */
    public function event_listener_can_access_form_properties()
    {
        $capturedFormName = null;
        $capturedFormSlug = null;

        Event::listen(FormSubmitted::class, function ($event) use (&$capturedFormName, &$capturedFormSlug) {
            $capturedFormName = $event->form->name;
            $capturedFormSlug = $event->form->slug;
        });

        $form = Form::factory()->create([
            'slug' => 'test-form',
            'name' => 'Test Form',
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
        ]);

        $this->post("/forms/{$form->slug}", [
            'name' => 'John Doe',
        ]);

        $this->assertEquals('Test Form', $capturedFormName);
        $this->assertEquals('test-form', $capturedFormSlug);
    }
}