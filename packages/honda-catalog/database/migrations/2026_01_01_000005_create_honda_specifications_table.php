<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honda_specifications', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->foreignId('model_id')->constrained('honda_models')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()
                ->constrained('honda_variants')->cascadeOnDelete();
            $table->string('section');
            $table->string('category');
            $table->string('label');
            $table->text('value')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['model_id', 'section', 'category'], 'honda_specs_model_section_category_idx');
            $table->index(['model_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honda_specifications');
    }
};
