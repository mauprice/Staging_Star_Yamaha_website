<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_owned_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('make');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->string('category')->default('Motorcycle'); // Motorcycle, Watercraft, ATV, etc.
            $table->unsignedInteger('kms')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('condition')->default('Used'); // Used, Demo, Dealer Demo
            $table->string('colour')->nullable();
            $table->string('rego')->nullable();
            $table->text('description')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('images')->nullable();  // additional gallery images
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_owned_listings');
    }
};
