<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Unit;

use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormField;
use Signalfire\FilamentFormsBuilder\Models\FormSubmission;
use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class FormSubmissionModelTest extends TestCase
{
    /** @test */
    public function it_can_create_a_form_submission()
    {
        $form = Form::factory()->create();
        $submission = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'data' => ['name' => 'John Doe', 'email' => 'john@example.com'],
        ]);

        $this->assertInstanceOf(FormSubmission::class, $submission);
        $this->assertEquals($form->id, $submission->form_id);
        $this->assertEquals(['name' => 'John Doe', 'email' => 'john@example.com'], $submission->data);
    }

    /** @test */
    public function it_belongs_to_a_form()
    {
        $form = Form::factory()->create();
        $submission = FormSubmission::factory()->create(['form_id' => $form->id]);

        $this->assertInstanceOf(Form::class, $submission->form);
        $this->assertEquals($form->id, $submission->form->id);
    }

    /** @test */
    public function it_automatically_sets_submitted_at_when_creating()
    {
        $form = Form::factory()->create();
        $submission = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'submitted_at' => null,
        ]);

        $this->assertNotNull($submission->submitted_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $submission->submitted_at);
    }

    /** @test */
    public function it_can_get_field_value()
    {
        $submission = FormSubmission::factory()->create([
            'data' => ['name' => 'John Doe', 'email' => 'john@example.com'],
        ]);

        $this->assertEquals('John Doe', $submission->getFieldValue('name'));
        $this->assertEquals('john@example.com', $submission->getFieldValue('email'));
        $this->assertNull($submission->getFieldValue('phone'));
    }

    /** @test */
    public function it_can_check_if_field_exists()
    {
        $submission = FormSubmission::factory()->create([
            'data' => ['name' => 'John Doe', 'email' => null],
        ]);

        $this->assertTrue($submission->hasField('name'));
        $this->assertTrue($submission->hasField('email')); // exists but null
        $this->assertFalse($submission->hasField('phone'));
    }

    /** @test */
    public function it_can_get_formatted_data()
    {
        $form = Form::factory()->create();
        $nameField = FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Full Name',
            'type' => 'text',
        ]);
        $emailField = FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'label' => 'Email Address',
            'type' => 'email',
        ]);

        $submission = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'data' => ['name' => 'John Doe', 'email' => 'john@example.com'],
        ]);

        $formatted = $submission->getFormattedData();

        $this->assertEquals([
            'Full Name' => 'John Doe',
            'Email Address' => 'john@example.com',
        ], $formatted);
    }

    /** @test */
    public function it_formats_array_values_as_comma_separated()
    {
        $form = Form::factory()->create();
        $field = FormField::factory()->checkbox()->create([
            'form_id' => $form->id,
            'key' => 'interests',
            'label' => 'Interests',
        ]);

        $submission = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'data' => ['interests' => ['newsletter', 'updates']],
        ]);

        $formatted = $submission->getFormattedData();

        $this->assertEquals(['Interests' => 'newsletter, updates'], $formatted);
    }

    /** @test */
    public function it_formats_boolean_checkbox_values()
    {
        $form = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'newsletter',
            'label' => 'Subscribe to Newsletter',
            'type' => 'checkbox',
        ]);

        $submission1 = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'data' => ['newsletter' => true],
        ]);

        $submission2 = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'data' => ['newsletter' => false],
        ]);

        $formatted1 = $submission1->getFormattedData();
        $formatted2 = $submission2->getFormattedData();

        $this->assertEquals(['Subscribe to Newsletter' => 'Yes'], $formatted1);
        $this->assertEquals(['Subscribe to Newsletter' => 'No'], $formatted2);
    }

    /** @test */
    public function it_formats_select_and_radio_values_using_options()
    {
        $form = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'country',
            'label' => 'Country',
            'type' => 'select',
            'options' => [
                ['value' => 'us', 'label' => 'United States'],
                ['value' => 'ca', 'label' => 'Canada'],
            ],
        ]);

        $submission = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'data' => ['country' => 'us'],
        ]);

        $formatted = $submission->getFormattedData();

        $this->assertEquals(['Country' => 'United States'], $formatted);
    }

    /** @test */
    public function it_returns_original_value_when_option_not_found()
    {
        $form = Form::factory()->create();
        $field = FormField::factory()->select()->create([
            'form_id' => $form->id,
            'key' => 'choice',
            'label' => 'Choice',
        ]);

        $submission = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'data' => ['choice' => 'unknown_value'],
        ]);

        $formatted = $submission->getFormattedData();

        $this->assertEquals(['Choice' => 'unknown_value'], $formatted);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $submission = FormSubmission::factory()->create([
            'data' => ['name' => 'John'],
            'submitted_at' => '2023-01-01 12:00:00',
        ]);

        $this->assertIsArray($submission->data);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $submission->submitted_at);
    }

    /** @test */
    public function it_skips_null_values_in_formatted_data()
    {
        $form = Form::factory()->create();
        $field1 = FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Name',
        ]);
        $field2 = FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'label' => 'Email',
        ]);

        $submission = FormSubmission::factory()->create([
            'form_id' => $form->id,
            'data' => ['name' => 'John Doe'], // email is missing
        ]);

        $formatted = $submission->getFormattedData();

        $this->assertEquals(['Name' => 'John Doe'], $formatted);
        $this->assertArrayNotHasKey('Email', $formatted);
    }
}