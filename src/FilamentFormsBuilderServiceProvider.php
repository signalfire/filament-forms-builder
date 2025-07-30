<?php

namespace Signalfire\FilamentFormsBuilder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Signalfire\FilamentFormsBuilder\Components\FormComponent;

class FilamentFormsBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-forms-builder.php', 'filament-forms-builder');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-forms-builder');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/filament-forms-builder.php' => config_path('filament-forms-builder.php'),
            ], 'filament-forms-builder-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-forms-builder'),
            ], 'filament-forms-builder-views');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        // Register Blade component
        Blade::component('filament-forms-builder::form', FormComponent::class);
    }
}