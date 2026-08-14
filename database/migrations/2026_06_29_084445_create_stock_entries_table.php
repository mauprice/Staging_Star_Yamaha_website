<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();

            // Vehicle identification
            $table->string('description')->nullable();
            $table->string('stock_no')->nullable()->index();
            $table->string('vin_no')->nullable()->index();
            $table->string('rego_no')->nullable();
            $table->string('ymaf_unit_no')->nullable();

            // Purchase
            $table->date('purchase_date')->nullable()->index();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('price_before_writeback', 10, 2)->default(0);
            $table->decimal('writeback', 10, 2)->default(0);

            // Acquisition costs
            $table->decimal('revs_transfer_fee', 10, 2)->default(0);
            $table->decimal('rego_fee', 10, 2)->default(0);
            $table->decimal('stamp_duty', 10, 2)->default(0);
            $table->decimal('workshop_parts', 10, 2)->default(0);
            $table->decimal('workshop_labour', 10, 2)->default(0);
            $table->decimal('transport', 10, 2)->default(0);
            $table->decimal('misc', 10, 2)->default(0);
            $table->decimal('accessories_purchased', 10, 2)->default(0);
            $table->decimal('insurance', 10, 2)->default(0);
            $table->decimal('ymaf_rebate', 10, 2)->default(0);

            // GST (AU 10%, extracted from purchase price)
            $table->decimal('gst_paid', 10, 2)->default(0);
            $table->decimal('gst_collected', 10, 2)->default(0);
            $table->decimal('gst_payable', 10, 2)->default(0);

            // Trade-in
            $table->string('trade_details')->nullable();
            $table->decimal('trade_in_price', 10, 2)->default(0);

            // Sale
            $table->date('sold_date')->nullable()->index();
            $table->string('sold_to')->nullable();
            $table->string('inv_no')->nullable();
            $table->string('salesman')->nullable()->index();
            $table->decimal('bal_paid', 10, 2)->default(0);
            $table->decimal('sold_price', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->date('comm_paid_date')->nullable();

            // Banking
            $table->decimal('bal_banked', 10, 2)->default(0);
            $table->date('date_banked')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_entries');
    }
};
