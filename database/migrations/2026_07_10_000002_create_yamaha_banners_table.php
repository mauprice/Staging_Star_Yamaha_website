<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yamaha_banners', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('product_id')->index();
            $table->string('image')->nullable();
            $table->text('image_options')->nullable();
            $table->tinyInteger('image_type')->nullable(); // 1 = hero, 2 = additional
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('yamaha_products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yamaha_banners');
    }
};
