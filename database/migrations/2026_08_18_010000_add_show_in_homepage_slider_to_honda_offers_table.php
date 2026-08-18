<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('honda_offers', function (Blueprint $table) {
            $table->boolean('show_in_homepage_slider')->default(false)->after('is_active');
        });

        // Preserve current homepage behaviour: whatever already qualifies for
        // the slider under the old fixed rules (top-level, active, has an
        // image) starts opted in. The admin can then curate from there.
        DB::table('honda_offers')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->whereNotNull('image_asset_id')
            ->update(['show_in_homepage_slider' => true]);
    }

    public function down(): void
    {
        Schema::table('honda_offers', function (Blueprint $table) {
            $table->dropColumn('show_in_homepage_slider');
        });
    }
};
