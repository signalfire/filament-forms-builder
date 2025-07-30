<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('filament-forms-builder.table_names.form_submissions', 'form_submissions'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained(config('filament-forms-builder.table_names.forms', 'forms'))->onDelete('cascade');
            $table->json('data');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['form_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-forms-builder.table_names.form_submissions', 'form_submissions'));
    }
};