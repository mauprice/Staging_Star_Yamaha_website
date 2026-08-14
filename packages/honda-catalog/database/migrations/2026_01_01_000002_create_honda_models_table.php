<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honda_models', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('subcategory');
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedInteger('price_from')->nullable();
            $table->string('price_currency', 3)->default('AUD');
            $table->string('price_label')->nullable();
            $table->text('source_url');
            $table->foreignId('og_image_asset_id')->nullable()
                ->constrained('honda_assets')->nullOnDelete();
            $table->timestamp('last_scraped_at')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['category', 'subcategory']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honda_models');
    }
};
