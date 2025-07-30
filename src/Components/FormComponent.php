<?php

namespace Signalfire\FilamentFormsBuilder\Components;

use Illuminate\View\Component;
use Signalfire\FilamentFormsBuilder\Models\Form;

class FormComponent extends Component
{
    public Form $form;
    public array $errors;
    public array $old;

    public function __construct(
        public string $slug,
        array $errors = [],
        array $old = []
    ) {
        $this->form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with(['fields' => function ($query) {
                $query->where('is_visible', true)->orderBy('sort_order');
            }])
            ->firstOrFail();

        $this->errors = $errors;
        $this->old = $old;
    }

    public function render()
    {
        return view('filament-forms-builder::components.form', [
            'form' => $this->form,
            'errors' => $this->errors,
            'old' => $this->old,
            'component' => $this,
        ]);
    }

    public function getFieldValue(string $key)
    {
        return $this->old[$key] ?? null;
    }

    public function hasError(string $key): bool
    {
        return isset($this->errors[$key]);
    }

    public function getError(string $key): ?string
    {
        return $this->errors[$key][0] ?? null;
    }

    public function getColumnClass(): string
    {
        return match ($this->form->columns) {
            2 => 'grid-cols-1 md:grid-cols-2',
            3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
            default => 'grid-cols-1',
        };
    }

    public function getFieldColumnSpan(int $columnSpan): string
    {
        if ($this->form->columns === 1) {
            return 'col-span-1';
        }

        return match ($columnSpan) {
            2 => 'col-span-1 md:col-span-1',
            3 => 'col-span-1 md:col-span-1 lg:col-span-1',
            default => $this->form->columns > 1 ? 'col-span-full' : 'col-span-1',
        };
    }
}