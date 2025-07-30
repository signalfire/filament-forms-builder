<?php

namespace Signalfire\FilamentFormsBuilder;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Signalfire\FilamentFormsBuilder\Resources\FormResource;

class FilamentFormsBuilderPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-forms-builder';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            FormResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }
}