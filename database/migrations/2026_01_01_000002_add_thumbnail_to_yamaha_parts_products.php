<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yamaha_parts_products', function (Blueprint $table) {
            $table->integer('thumbnail_image_id')->nullable()->after('has_images');
        });
    }

    public function down(): void
    {
        Schema::table('yamaha_parts_products', function (Blueprint $table) {
            $table->dropColumn('thumbnail_image_id');
        });
    }
};
