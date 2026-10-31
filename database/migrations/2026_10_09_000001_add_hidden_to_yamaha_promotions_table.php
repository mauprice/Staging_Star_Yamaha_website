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
        Schema::table('yamaha_promotions', function (Blueprint $table) {
            // Admin-controlled visibility override, independent of the API's
            // own `active` flag. Never written by yamaha:sync, so a manual
            // hide survives every re-sync (see YamahaPromotion's global scope).
            $table->boolean('hidden')->default(false)->after('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yamaha_promotions', function (Blueprint $table) {
            $table->dropColumn('hidden');
        });
    }
};
