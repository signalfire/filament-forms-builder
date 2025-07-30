<?php

namespace Signalfire\FilamentFormsBuilder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory;
    protected $fillable = [
        'form_id',
        'data',
        'ip_address',
        'user_agent',
        'submitted_at',
    ];

    protected $casts = [
        'data' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getDataAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->table = config('filament-forms-builder.table_names.form_submissions', 'form_submissions');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($submission) {
            if (empty($submission->submitted_at)) {
                $submission->submitted_at = now();
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function getFieldValue(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function hasField(string $key): bool
    {
        return array_key_exists($key, $this->data ?? []);
    }

    public function getFormattedData(): array
    {
        $formatted = [];
        
        if (empty($this->data) || !$this->form) {
            return $formatted;
        }

        foreach ($this->form->fields as $field) {
            $value = $this->getFieldValue($field->key);
            
            if ($value !== null) {
                $formatted[$field->label] = $this->formatFieldValue($field, $value);
            }
        }

        return $formatted;
    }

    protected function formatFieldValue(FormField $field, mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }

        if ($field->type === 'checkbox' && is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (in_array($field->type, ['select', 'radio']) && !empty($field->options)) {
            $options = $field->getOptionsForSelect();
            return $options[$value] ?? $value;
        }

        return (string) $value;
    }

    protected static function newFactory()
    {
        return \Signalfire\FilamentFormsBuilder\Database\Factories\FormSubmissionFactory::new();
    }
}