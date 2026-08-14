<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honda_colours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('honda_models')->cascadeOnDelete();
            $table->string('name');
            $table->string('hex', 7)->nullable();
            $table->foreignId('image_asset_id')->nullable()
                ->constrained('honda_assets')->nullOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index('model_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honda_colours');
    }
};
