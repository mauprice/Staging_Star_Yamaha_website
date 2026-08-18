<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->string('colour')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('price_override', 10, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'size', 'colour']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
