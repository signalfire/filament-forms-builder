<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Unit;

use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormField;
use Signalfire\FilamentFormsBuilder\Models\FormSubmission;
use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class FormModelTest extends TestCase
{
    /** @test */
    public function it_can_create_a_form()
    {
        $form = Form::factory()->create([
            'name' => 'Contact Form',
            'description' => 'A simple contact form',
        ]);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('Contact Form', $form->name);
        $this->assertEquals('contact-form', $form->slug);
        $this->assertEquals('A simple contact form', $form->description);
        $this->assertTrue($form->is_active);
    }

    /** @test */
    public function it_automatically_generates_slug_from_name()
    {
        $form = Form::factory()->create(['name' => 'My Complex Form Name']);
        
        $this->assertEquals('my-complex-form-name', $form->slug);
    }

    /** @test */
    public function it_can_have_multiple_fields()
    {
        $form = Form::factory()->create();
        $field1 = FormField::factory()->create(['form_id' => $form->id]);
        $field2 = FormField::factory()->create(['form_id' => $form->id]);

        $this->assertCount(2, $form->fields);
        $this->assertTrue($form->fields->contains($field1));
        $this->assertTrue($form->fields->contains($field2));
    }

    /** @test */
    public function it_can_have_submissions()
    {
        $form = Form::factory()->create();
        $submission1 = FormSubmission::factory()->create(['form_id' => $form->id]);
        $submission2 = FormSubmission::factory()->create(['form_id' => $form->id]);

        $this->assertCount(2, $form->submissions);
        $this->assertTrue($form->submissions->contains($submission1));
        $this->assertTrue($form->submissions->contains($submission2));
    }

    /** @test */
    public function it_returns_correct_submission_url()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        $this->assertEquals(
            route('filament-forms-builder.submit', 'contact-us'),
            $form->getSubmissionUrl()
        );
    }

    /** @test */
    public function it_returns_custom_route_when_set()
    {
        $customRoute = 'https://example.com/webhook';
        $form = Form::factory()->withCustomRoute()->create(['custom_route' => $customRoute]);
        
        $this->assertEquals($customRoute, $form->getSubmissionUrl());
    }

    /** @test */
    public function it_returns_correct_public_url()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        $this->assertEquals(
            route('filament-forms-builder.show', 'contact-us'),
            $form->getPublicUrl()
        );
    }

    /** @test */
    public function it_can_scope_active_forms()
    {
        Form::factory()->create(['is_active' => true]);
        Form::factory()->inactive()->create();

        $activeForms = Form::active()->get();
        
        $this->assertCount(1, $activeForms);
        $this->assertTrue($activeForms->first()->is_active);
    }

    /** @test */
    public function it_uses_slug_as_route_key()
    {
        $form = Form::factory()->create();
        
        $this->assertEquals('slug', $form->getRouteKeyName());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $form = Form::factory()->create([
            'is_active' => '1',
            'columns' => '2',
            'settings' => ['custom' => 'value'],
        ]);

        $this->assertIsBool($form->is_active);
        $this->assertIsInt($form->columns);
        $this->assertIsArray($form->settings);
        $this->assertEquals(['custom' => 'value'], $form->settings);
    }

    /** @test */
    public function it_orders_fields_by_sort_order()
    {
        $form = Form::factory()->create();
        $field1 = FormField::factory()->sortOrder(2)->create(['form_id' => $form->id]);
        $field2 = FormField::factory()->sortOrder(1)->create(['form_id' => $form->id]);
        $field3 = FormField::factory()->sortOrder(3)->create(['form_id' => $form->id]);

        $orderedFields = $form->fields;
        
        $this->assertEquals($field2->id, $orderedFields[0]->id);
        $this->assertEquals($field1->id, $orderedFields[1]->id);
        $this->assertEquals($field3->id, $orderedFields[2]->id);
    }

    /** @test */
    public function it_orders_submissions_by_latest_first()
    {
        $form = Form::factory()->create();
        $oldSubmission = FormSubmission::factory()->old()->create(['form_id' => $form->id]);
        $recentSubmission = FormSubmission::factory()->recent()->create(['form_id' => $form->id]);

        $submissions = $form->submissions;
        
        $this->assertEquals($recentSubmission->id, $submissions[0]->id);
        $this->assertEquals($oldSubmission->id, $submissions[1]->id);
    }
}