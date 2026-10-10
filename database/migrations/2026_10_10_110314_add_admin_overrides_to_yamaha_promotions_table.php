<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `active`/`sort_index` are overwritten from the Yamaha API on every
 * `yamaha:sync` run, so they can't double as admin-controlled fields.
 * These two columns are admin-only: the sync populates them just once,
 * on first insert, and never touches them again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yamaha_promotions', function (Blueprint $table) {
            $table->boolean('is_visible')->nullable()->after('active');
            $table->integer('display_order')->nullable()->after('sort_index');
        });

        DB::table('yamaha_promotions')->update([
            'is_visible'    => DB::raw('active'),
            'display_order' => DB::raw('sort_index'),
        ]);
    }

    public function down(): void
    {
        Schema::table('yamaha_promotions', function (Blueprint $table) {
            $table->dropColumn(['is_visible', 'display_order']);
        });
    }
};
