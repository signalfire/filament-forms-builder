<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('filament-forms-builder.table_names.forms', 'forms'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('success_message')->default('Thank you! Your form has been submitted successfully.');
            $table->string('submit_button_text')->default('Submit');
            $table->integer('columns')->default(1);
            $table->string('custom_route')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-forms-builder.table_names.forms', 'forms'));
    }
};