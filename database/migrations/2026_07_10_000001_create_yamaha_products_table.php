<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yamaha_products', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('model_name', 150)->nullable();
            $table->string('product_type', 50)->nullable();
            $table->year('year_model')->nullable();
            $table->string('division', 50)->nullable();
            $table->string('product_group', 50)->nullable();
            $table->string('sub_category', 100)->nullable();
            $table->string('primary_category', 100)->nullable();
            $table->string('item_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->text('long_description')->nullable();
            $table->string('summary_image')->nullable();
            $table->decimal('recommended_retail', 10, 2)->nullable();
            $table->decimal('recommended_retail_nz', 10, 2)->nullable();
            $table->string('brochure_url')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yamaha_products');
    }
};
