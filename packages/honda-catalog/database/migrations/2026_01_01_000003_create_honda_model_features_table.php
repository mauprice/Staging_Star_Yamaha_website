<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honda_model_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('honda_models')->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->string('heading');
            $table->text('body')->nullable();
            $table->foreignId('image_asset_id')->nullable()
                ->constrained('honda_assets')->nullOnDelete();
            $table->timestamps();

            $table->index(['model_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honda_model_features');
    }
};
