<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yamaha_parts_prices', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('part_number', 30)->primary();
            $table->string('description', 60)->nullable();
            $table->unsignedInteger('rrp_cents')->default(0);
            $table->unsignedInteger('cost_cents')->default(0);
            $table->string('currency', 3)->default('AUD');
            $table->string('source_file', 30)->nullable();
            $table->timestamp('imported_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yamaha_parts_prices');
    }
};
