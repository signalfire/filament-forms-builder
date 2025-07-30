<?php

namespace Signalfire\FilamentFormsBuilder\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Signalfire\FilamentFormsBuilder\Models\Form;

class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'success_message' => 'Thank you! Your form has been submitted successfully.',
            'submit_button_text' => 'Submit',
            'columns' => $this->faker->numberBetween(1, 3),
            'custom_route' => null,
            'is_active' => true,
            'settings' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withCustomRoute(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_route' => 'https://example.com/webhook',
        ]);
    }

    public function multiColumn(int $columns = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'columns' => $columns,
        ]);
    }
}