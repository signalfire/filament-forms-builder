<?php

namespace Signalfire\FilamentFormsBuilder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'success_message',
        'submit_button_text',
        'columns',
        'custom_route',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'columns' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->table = config('filament-forms-builder.table_names.forms', 'forms');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->name);
            }
        });
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest('submitted_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSubmissionUrl(): string
    {
        if ($this->custom_route) {
            return $this->custom_route;
        }

        return route('filament-forms-builder.submit', $this->slug);
    }

    public function getPublicUrl(): string
    {
        return route('filament-forms-builder.show', $this->slug);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory()
    {
        return \Signalfire\FilamentFormsBuilder\Database\Factories\FormFactory::new();
    }
}