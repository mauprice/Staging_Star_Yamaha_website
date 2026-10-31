<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            // Nullable: generated from the auto-increment id in
            // Quote::booted() after the initial insert, same pattern as
            // orders.order_number.
            $table->string('quote_number')->nullable()->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot, same reasoning as orders.customer_name - a quote
            // should still show who it was for even if the customer record
            // is later edited, and walk-ins without a Customer still need a
            // name to put on the printed quote.
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();

            // Set once this quote is loaded back into POS and the sale
            // completes - a quote is never "paid" itself, the order it
            // produces is.
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('converted_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
