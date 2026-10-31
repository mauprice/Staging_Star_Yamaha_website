<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('id')->constrained()->nullOnDelete();

            $table->string('location', 30)->nullable()->after('stock_quantity');
            $table->unsignedInteger('min_stock')->nullable()->after('location');
            $table->unsignedInteger('max_stock')->nullable()->after('min_stock');
            $table->unsignedInteger('reorder_qty')->nullable()->after('max_stock');
            $table->string('reorder_code', 20)->nullable()->after('reorder_qty');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn(['location', 'min_stock', 'max_stock', 'reorder_qty', 'reorder_code']);
        });
    }
};
