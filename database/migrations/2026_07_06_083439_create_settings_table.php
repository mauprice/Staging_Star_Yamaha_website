<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
        });

        DB::table('settings')->insert([
            ['key' => 'commission_rate',    'value' => '10'],
            ['key' => 'commission_minimum', 'value' => '50'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
