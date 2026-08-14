<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honda_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('honda_models')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['model_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honda_variants');
    }
};
