<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salespeople', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('stock_entries')
            ->whereNotNull('salesman')
            ->where('salesman', '!=', '')
            ->distinct()
            ->pluck('salesman')
            ->map(fn (string $n) => trim($n))
            ->filter()
            ->unique()
            ->each(function (string $name) {
                DB::table('salespeople')->insertOrIgnore([
                    'name'       => $name,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('salespeople');
    }
};
