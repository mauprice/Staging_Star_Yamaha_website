<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // A sale can mix fulfilled and back-ordered lines - when a cart
            // line exceeds available stock, it's split into two OrderItems
            // rather than one item carrying a part-fulfilled quantity.
            $table->string('fulfillment_status')->default('fulfilled')->index()->after('line_total');
            $table->date('expected_date')->nullable()->after('fulfillment_status');
            $table->text('fulfillment_notes')->nullable()->after('expected_date');
            $table->timestamp('fulfilled_at')->nullable()->after('fulfillment_notes');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_status', 'expected_date', 'fulfillment_notes', 'fulfilled_at']);
        });
    }
};
