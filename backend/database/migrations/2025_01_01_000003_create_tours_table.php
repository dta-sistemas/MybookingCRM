<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            // Duración en minutos evita ambigüedad de formato ("4 horas", "medio día", etc.)
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('draft'); // draft | active | inactive
            $table->boolean('featured')->default(false);
            $table->string('language', 5)->default('es');

            // SEO básico (se amplía en Fase 3/7)
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
