<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A cart item is either a Shop Accessories product OR an OEM part
        // from the parts-finder catalogue (a separate package with its own
        // table/PK, no stock tracking, and price looked up by number rather
        // than stored on the model) - product_id has to become optional so
        // a part-only row doesn't need one.
        DB::statement('ALTER TABLE cart_items MODIFY product_id BIGINT UNSIGNED NULL');

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('part_number')->nullable()->after('product_variant_id');
            $table->string('part_description')->nullable()->after('part_number');
            // Snapshotted at add-to-cart time (server-derived, never trusted
            // from the client) since Part has no price column of its own -
            // it's looked up from a separate Price table and markup-adjusted.
            $table->decimal('unit_price_snapshot', 10, 2)->nullable()->after('part_description');
            $table->string('currency', 3)->nullable()->after('unit_price_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['part_number', 'part_description', 'unit_price_snapshot', 'currency']);
        });

        DB::statement('ALTER TABLE cart_items MODIFY product_id BIGINT UNSIGNED NOT NULL');
    }
};
