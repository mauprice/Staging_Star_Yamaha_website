<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->text('staff_reply')->nullable()->after('notes');
            $table->date('alt_date_1')->nullable()->after('staff_reply');
            $table->date('alt_date_2')->nullable()->after('alt_date_1');
            $table->date('alt_date_3')->nullable()->after('alt_date_2');
            $table->timestamp('replied_at')->nullable()->after('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropColumn(['staff_reply', 'alt_date_1', 'alt_date_2', 'alt_date_3', 'replied_at']);
        });
    }
};
