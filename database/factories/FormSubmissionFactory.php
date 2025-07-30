<?php

namespace Signalfire\FilamentFormsBuilder\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormSubmission;

class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'data' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'message' => $this->faker->paragraph(),
            ],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'submitted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function withCustomData(array $data): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => $data,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => now()->subMinutes($this->faker->numberBetween(1, 60)),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => now()->subDays($this->faker->numberBetween(30, 365)),
        ]);
    }
}