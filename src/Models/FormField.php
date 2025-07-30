<?php

namespace Signalfire\FilamentFormsBuilder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasFactory;
    protected $fillable = [
        'form_id',
        'key',
        'label',
        'type',
        'validation_rules',
        'default_value',
        'options',
        'placeholder',
        'help_text',
        'column_span',
        'sort_order',
        'is_required',
        'is_visible',
        'conditional_logic',
    ];

    protected $casts = [
        'options' => 'array',
        'conditional_logic' => 'array',
        'is_required' => 'boolean',
        'is_visible' => 'boolean',
        'column_span' => 'integer',
        'sort_order' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->table = config('filament-forms-builder.table_names.form_fields', 'form_fields');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function getValidationRulesArray(): array
    {
        $rules = [];
        
        if (!empty($this->validation_rules)) {
            $rules = explode('|', $this->validation_rules);
        }
        
        // Automatically add 'required' rule if field is marked as required
        if ($this->is_required && !in_array('required', $rules)) {
            array_unshift($rules, 'required');
        }
        
        return $rules;
    }

    public function getFieldTypeLabel(): string
    {
        $fieldTypes = config('filament-forms-builder.field_types', []);
        return $fieldTypes[$this->type] ?? ucfirst($this->type);
    }

    public function getOptionsForSelect(): array
    {
        if (!in_array($this->type, ['select', 'radio', 'checkbox']) || empty($this->options)) {
            return [];
        }

        $options = [];
        foreach ($this->options as $option) {
            if (is_array($option) && isset($option['value'], $option['label'])) {
                $options[$option['value']] = $option['label'];
            } elseif (is_string($option)) {
                $options[$option] = $option;
            }
        }

        return $options;
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($field) {
            if (empty($field->key) && !empty($field->label)) {
                $field->key = static::generateKeyFromLabel($field->label);
            }
        });

        static::updating(function ($field) {
            if (empty($field->key) && !empty($field->label)) {
                $field->key = static::generateKeyFromLabel($field->label);
            }
        });
    }

    protected static function generateKeyFromLabel(string $label): string
    {
        return \Illuminate\Support\Str::slug($label, '_');
    }

    protected static function newFactory()
    {
        return \Signalfire\FilamentFormsBuilder\Database\Factories\FormFieldFactory::new();
    }
}