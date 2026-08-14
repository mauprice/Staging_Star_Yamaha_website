<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('yamaha_news', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('head')->nullable();
            $table->text('brief')->nullable();
            $table->string('brief_image')->nullable();
            $table->string('full_content_url')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->text('image_options')->nullable();
            $table->string('type', 100)->nullable();
            $table->json('other_types')->nullable();
            $table->string('country', 10)->default('AU');
            $table->boolean('active')->default(true);
            $table->integer('sort_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yamaha_news');
    }
};
