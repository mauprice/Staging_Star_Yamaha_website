<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honda_offers', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('honda_offers')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('price_label')->nullable();
            $table->text('body')->nullable();
            $table->foreignId('image_asset_id')->nullable()->constrained('honda_assets')->nullOnDelete();
            $table->text('cta_url')->nullable();
            $table->string('cta_label')->nullable();
            $table->foreignId('honda_model_id')->nullable()->constrained('honda_models')->nullOnDelete();
            $table->text('source_url');
            $table->unsignedInteger('sort')->default(0);
            $table->string('content_hash')->nullable();
            $table->timestamp('last_scraped_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honda_offers');
    }
};
