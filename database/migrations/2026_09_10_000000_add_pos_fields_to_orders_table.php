<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source')->default('online')->after('order_number');
            $table->foreignId('cashier_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->decimal('tax_total', 10, 2)->nullable()->after('total');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cashier_id');
            $table->dropIndex(['source']);
            $table->dropColumn(['source', 'tax_total']);
        });
    }
};
