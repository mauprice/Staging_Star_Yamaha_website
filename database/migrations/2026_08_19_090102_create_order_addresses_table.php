<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // shipping | billing

            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('suburb');
            $table->string('state', 10);
            $table->string('postcode', 10);
            $table->string('country', 2)->default('AU');

            $table->timestamps();

            $table->unique(['order_id', 'type']);
            // Phase 5's rate-table lookups filter/join on these directly.
            $table->index(['country', 'state', 'postcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
    }
};
