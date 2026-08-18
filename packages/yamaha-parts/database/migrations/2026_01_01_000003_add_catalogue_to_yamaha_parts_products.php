<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yamaha_parts_products', function (Blueprint $table) {
            $table->char('catalogue', 2)->default('MB')->after('product_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('yamaha_parts_products', function (Blueprint $table) {
            $table->dropColumn('catalogue');
        });
    }
};
