<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('filament-forms-builder.table_names.form_fields', 'form_fields'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained(config('filament-forms-builder.table_names.forms', 'forms'))->onDelete('cascade');
            $table->string('key')->index();
            $table->string('label');
            $table->string('type');
            $table->text('validation_rules')->nullable();
            $table->text('default_value')->nullable();
            $table->json('options')->nullable(); // For select, radio, checkbox options
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->integer('column_span')->default(1);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->json('conditional_logic')->nullable(); // For future conditional visibility
            $table->timestamps();

            $table->unique(['form_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-forms-builder.table_names.form_fields', 'form_fields'));
    }
};