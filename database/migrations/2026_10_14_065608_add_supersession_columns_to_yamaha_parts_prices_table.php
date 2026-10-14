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
        Schema::table('yamaha_parts_prices', function (Blueprint $table) {
            $table->string('superseded_by', 30)->nullable()->after('cost_cents');
            $table->string('superseded_note', 20)->nullable()->after('superseded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yamaha_parts_prices', function (Blueprint $table) {
            $table->dropColumn(['superseded_by', 'superseded_note']);
        });
    }
};
