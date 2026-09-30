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
        Schema::table('yamaha_products', function (Blueprint $table) {
            // Manually excluded from the public site (e.g. superseded model-year
            // SKUs Yamaha's API still returns but never gave marketing images for).
            // Never written by yamaha:sync, so a manual hide survives every re-sync.
            $table->boolean('hidden')->default(false)->after('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yamaha_products', function (Blueprint $table) {
            $table->dropColumn('hidden');
        });
    }
};
