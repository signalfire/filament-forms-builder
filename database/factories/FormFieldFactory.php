<?php

namespace Signalfire\FilamentFormsBuilder\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormField;

class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        $label = $this->faker->words(2, true);
        $types = ['text', 'textarea', 'email', 'select', 'checkbox', 'radio', 'date', 'number'];
        
        return [
            'form_id' => Form::factory(),
            'key' => \Illuminate\Support\Str::slug($label, '_'),
            'label' => ucfirst($label),
            'type' => $this->faker->randomElement($types),
            'validation_rules' => null,
            'default_value' => null,
            'options' => null,
            'placeholder' => $this->faker->sentence(3),
            'help_text' => null,
            'column_span' => 1,
            'sort_order' => 0,
            'is_required' => $this->faker->boolean(30),
            'is_visible' => true,
            'conditional_logic' => null,
        ];
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
            'validation_rules' => 'required',
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'email',
            'validation_rules' => 'email',
        ]);
    }

    public function select(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'select',
            'options' => [
                ['value' => 'option1', 'label' => 'Option 1'],
                ['value' => 'option2', 'label' => 'Option 2'],
                ['value' => 'option3', 'label' => 'Option 3'],
            ],
        ]);
    }

    public function radio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'radio',
            'options' => [
                ['value' => 'yes', 'label' => 'Yes'],
                ['value' => 'no', 'label' => 'No'],
            ],
        ]);
    }

    public function checkbox(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'checkbox',
            'options' => [
                ['value' => 'newsletter', 'label' => 'Subscribe to newsletter'],
                ['value' => 'updates', 'label' => 'Receive product updates'],
            ],
        ]);
    }

    public function withValidation(string $rules): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_rules' => $rules,
        ]);
    }

    public function columnSpan(int $span): static
    {
        return $this->state(fn (array $attributes) => [
            'column_span' => $span,
        ]);
    }

    public function sortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }
}