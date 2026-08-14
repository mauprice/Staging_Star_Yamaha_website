<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yamaha_banners', function (Blueprint $table) {
            // `image` remains the desktop crop. These two hold the mobile/tablet
            // crops the Yamaha API already provides via ImageOptions but which
            // were previously discarded (see design audit: hero crop bug).
            $table->string('image_mobile')->nullable()->after('image');
            $table->string('image_tablet')->nullable()->after('image_mobile');
        });
    }

    public function down(): void
    {
        Schema::table('yamaha_banners', function (Blueprint $table) {
            $table->dropColumn(['image_mobile', 'image_tablet']);
        });
    }
};
