<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yamaha_promotions', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('head', 255)->nullable();
            $table->text('brief')->nullable();
            $table->string('brief_image')->nullable();
            $table->string('full_content_url')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('type', 100)->nullable();
            $table->string('country', 10)->default('AU');
            $table->boolean('active')->default(true);
            $table->integer('sort_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yamaha_promotions');
    }
};
