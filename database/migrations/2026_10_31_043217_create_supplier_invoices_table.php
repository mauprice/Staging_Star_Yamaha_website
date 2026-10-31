<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // The supplier's own invoice/credit note number, not ours - not
            // globally unique since different suppliers number independently,
            // but a supplier shouldn't have the same number entered twice.
            $table->string('invoice_number');
            $table->string('type')->default('invoice');
            $table->date('invoice_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'invoice_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
    }
};
