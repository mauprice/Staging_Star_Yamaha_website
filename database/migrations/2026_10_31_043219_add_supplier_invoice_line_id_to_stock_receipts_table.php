<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_receipts', function (Blueprint $table) {
            // A receipt originates from either a PO receive or a directly
            // entered supplier invoice - never both.
            $table->foreignId('supplier_invoice_line_id')->nullable()->after('purchase_order_line_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_invoice_line_id');
        });
    }
};
