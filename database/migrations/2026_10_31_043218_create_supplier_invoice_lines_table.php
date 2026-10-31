<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot fields, same reasoning as order_items/purchase_order_lines.
            $table->string('product_name');
            $table->string('part_number')->nullable();

            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('line_total', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoice_lines');
    }
};
