<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honda_assets', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('guid')->unique();
            $table->text('source_url');
            $table->string('version_hash')->nullable();
            $table->string('host');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('bytes')->nullable();
            $table->string('local_path')->nullable();
            $table->string('storage_disk')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->string('status')->default('remote');
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();

            $table->index(['host', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honda_assets');
    }
};
