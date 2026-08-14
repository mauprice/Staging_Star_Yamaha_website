<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yamaha_colors', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id')->index();
            $table->string('color_name', 100)->nullable();
            $table->string('color_code', 20)->nullable();
            $table->string('color_image')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('yamaha_products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yamaha_colors');
    }
};
