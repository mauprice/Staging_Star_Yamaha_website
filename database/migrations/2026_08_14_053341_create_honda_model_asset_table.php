<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honda_model_asset', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->foreignId('model_id')->constrained('honda_models')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('honda_assets')->cascadeOnDelete();
            $table->string('role');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['model_id', 'asset_id', 'role'], 'honda_model_asset_unique');
            $table->index(['model_id', 'role', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honda_model_asset');
    }
};
