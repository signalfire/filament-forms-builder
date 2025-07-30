<?php

namespace Signalfire\FilamentFormsBuilder\Tests\Feature;

use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormField;
use Signalfire\FilamentFormsBuilder\Tests\TestCase;

class FormDisplayTest extends TestCase
{
    /** @test */
    public function it_can_display_an_active_form()
    {
        $form = Form::factory()->create([
            'slug' => 'contact-us',
            'name' => 'Contact Us',
            'is_active' => true,
        ]);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertStatus(200);
        $response->assertSee('Contact Us');
    }

    /** @test */
    public function it_returns_404_for_inactive_form()
    {
        $form = Form::factory()->inactive()->create(['slug' => 'contact-us']);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_non_existent_form()
    {
        $response = $this->get('/forms/non-existent');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_displays_form_description_when_present()
    {
        $form = Form::factory()->create([
            'slug' => 'contact-us',
            'description' => 'Please fill out this contact form',
        ]);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertSee('Please fill out this contact form');
    }

    /** @test */
    public function it_displays_visible_form_fields()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Your Name',
            'type' => 'text',
            'is_visible' => true,
        ]);

        FormField::factory()->hidden()->create([
            'form_id' => $form->id,
            'key' => 'hidden_field',
            'label' => 'Hidden Field',
        ]);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertSee('Your Name');
        $response->assertDontSee('Hidden Field');
    }

    /** @test */
    public function it_displays_fields_in_correct_order()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        $field1 = FormField::factory()->sortOrder(2)->create([
            'form_id' => $form->id,
            'label' => 'Second Field',
        ]);

        $field2 = FormField::factory()->sortOrder(1)->create([
            'form_id' => $form->id,
            'label' => 'First Field',
        ]);

        $field3 = FormField::factory()->sortOrder(3)->create([
            'form_id' => $form->id,
            'label' => 'Third Field',
        ]);

        $response = $this->get("/forms/{$form->slug}");
        $content = $response->getContent();

        $firstPos = strpos($content, 'First Field');
        $secondPos = strpos($content, 'Second Field');
        $thirdPos = strpos($content, 'Third Field');

        $this->assertTrue($firstPos < $secondPos);
        $this->assertTrue($secondPos < $thirdPos);
    }

    /** @test */
    public function it_displays_required_field_indicator()
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);
        
        FormField::factory()->required()->create([
            'form_id' => $form->id,
            'label' => 'Required Field',
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'label' => 'Optional Field',
            'is_required' => false,
        ]);

        $response = $this->get("/forms/{$form->slug}");

        // Should see asterisk for required field
        $response->assertSee('Required Field');
        $response->assertSee('*');
    }

    /** @test */
    public function it_displays_different_field_types_correctly()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);

        // Text field
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'type' => 'text',
            'placeholder' => 'Enter your name',
        ]);

        // Email field
        FormField::factory()->email()->create([
            'form_id' => $form->id,
            'key' => 'email',
        ]);

        // Textarea
        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'message',
            'type' => 'textarea',
        ]);

        // Select field
        FormField::factory()->select()->create([
            'form_id' => $form->id,
            'key' => 'country',
        ]);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertSee('type="text"', false);
        $response->assertSee('type="email"', false);
        $response->assertSee('<textarea', false);
        $response->assertSee('<select', false);
        $response->assertSee('Enter your name');
    }

    /** @test */
    public function it_displays_select_options()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'type' => 'select',
            'options' => [
                ['value' => 'us', 'label' => 'United States'],
                ['value' => 'ca', 'label' => 'Canada'],
            ],
        ]);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertSee('United States');
        $response->assertSee('Canada');
        $response->assertSee('value="us"', false);
        $response->assertSee('value="ca"', false);
    }

    /** @test */
    public function it_displays_radio_options()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        
        FormField::factory()->radio()->create([
            'form_id' => $form->id,
            'key' => 'choice',
        ]);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertSee('type="radio"', false);
        $response->assertSee('Yes');
        $response->assertSee('No');
    }

    /** @test */
    public function it_displays_checkbox_options()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        
        FormField::factory()->checkbox()->create([
            'form_id' => $form->id,
            'key' => 'interests',
        ]);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertSee('type="checkbox"', false);
        $response->assertSee('newsletter');
        $response->assertSee('updates');
    }

    /** @test */
    public function it_displays_help_text_when_present()
    {
        $form = Form::factory()->create(['slug' => 'test-form']);
        
        FormField::factory()->create([
            'form_id' => $form->id,
            'help_text' => 'This is helpful information',
        ]);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertSee('This is helpful information');
    }

    /** @test */
    public function it_displays_custom_submit_button_text()
    {
        $form = Form::factory()->create([
            'slug' => 'test-form',
            'submit_button_text' => 'Send Message',
        ]);

        $response = $this->get("/forms/{$form->slug}");

        $response->assertSee('Send Message');
        $response->assertDontSee('Submit');
    }

    /** @test */
    public function it_applies_correct_column_classes()
    {
        $singleColumnForm = Form::factory()->create([
            'slug' => 'single-column',
            'columns' => 1,
        ]);

        $multiColumnForm = Form::factory()->create([
            'slug' => 'multi-column',
            'columns' => 2,
        ]);

        $response1 = $this->get("/forms/{$singleColumnForm->slug}");
        $response2 = $this->get("/forms/{$multiColumnForm->slug}");

        $response1->assertSee('grid-cols-1');
        $response2->assertSee('grid-cols-1 md:grid-cols-2');
    }
}