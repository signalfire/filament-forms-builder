<?php

use Illuminate\Support\Facades\Route;
use Signalfire\FilamentFormsBuilder\Http\Controllers\FormController;

$routePrefix = config('filament-forms-builder.routes.prefix', 'forms');
$routeMiddleware = config('filament-forms-builder.routes.middleware', ['web']);

Route::prefix($routePrefix)
    ->middleware($routeMiddleware)
    ->name('filament-forms-builder.')
    ->group(function () {
        Route::get('/{slug}', [FormController::class, 'show'])->name('show');
        Route::post('/{slug}', [FormController::class, 'submit'])->name('submit');
    });