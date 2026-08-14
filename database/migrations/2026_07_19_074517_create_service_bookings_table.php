<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('email');
            $table->string('vehicle_year', 4);
            $table->string('vehicle_model');
            $table->string('rego', 20)->nullable();
            $table->unsignedInteger('odometer')->nullable();
            $table->string('service_type');
            $table->date('preferred_date');
            $table->string('preferred_time');
            $table->text('notes')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
