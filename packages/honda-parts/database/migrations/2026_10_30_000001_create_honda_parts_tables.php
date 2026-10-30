<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honda_parts_catalogues', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('catalogue_id')->primary();
            $table->string('number', 23)->nullable()->index();
            $table->string('year', 4)->nullable()->index();
            $table->string('version', 7)->nullable();
            $table->string('type', 2)->nullable();
            $table->string('path', 18)->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('honda_parts_models', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('model_id')->primary();
            $table->integer('catalogue_id')->index();
            $table->string('model', 28)->nullable()->index();
            $table->string('model_name', 36)->nullable()->index();
            $table->string('type', 148)->nullable();
            $table->string('cc', 9)->nullable();
            $table->string('year', 33)->nullable()->index();
            $table->string('code', 8)->nullable();
            $table->text('notes')->nullable();
            $table->integer('qty_col')->default(0);
            $table->boolean('duplicate')->default(false);
            $table->boolean('superceeded')->default(false);
            $table->timestamp('date_modified')->nullable();
            $table->timestamp('date_release')->nullable();
            $table->integer('category')->default(0);
            $table->boolean('online_enabled')->default(true);
            $table->integer('thumbnail_image_id')->nullable();
        });

        Schema::create('honda_parts_assemblies', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('assembly_id')->primary();
            $table->integer('catalogue_id')->index();
            $table->string('name', 100)->nullable();
            $table->string('address_no', 9)->nullable();
            $table->integer('page_no')->nullable();
            $table->boolean('is_accessory')->default(false);
            $table->boolean('is_adr')->default(false);
        });

        Schema::create('honda_parts_model_assemblies', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('model_assembly_id')->primary();
            $table->integer('model_id')->index();
            $table->integer('assembly_id')->index();
        });

        Schema::create('honda_parts_parts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('part_id')->primary();
            $table->integer('assembly_id')->index();
            $table->string('description', 100)->nullable();
            $table->integer('ref_no')->nullable()->index();
            $table->integer('ref_x')->nullable();
            $table->integer('ref_y')->nullable();
            $table->string('part_no', 20)->nullable()->index();
            $table->string('serial_no_from', 20)->nullable();
            $table->string('serial_no_to', 24)->nullable();
            $table->string('mangled_part_no', 20)->nullable()->index();
            $table->string('search_part_no', 19)->nullable()->index();
        });

        Schema::create('honda_parts_model_parts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('model_part_id')->primary();
            $table->integer('model_id')->index();
            $table->integer('part_id')->index();
            $table->integer('quantity')->default(1);
            $table->boolean('optional')->default(false);
        });

        Schema::create('honda_parts_assembly_images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('assembly_image_id')->primary();
            $table->integer('assembly_id')->index();
            $table->integer('image_id')->index();
            $table->integer('image_offset_x')->nullable();
            $table->integer('image_offset_y')->nullable();
        });

        Schema::create('honda_parts_images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->integer('catalogue_id')->index();
            $table->integer('image_id')->index();
            $table->string('filename', 40)->nullable();
            $table->string('format', 10)->default('webp');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->boolean('extracted')->default(false);
            $table->unique(['catalogue_id', 'image_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honda_parts_images');
        Schema::dropIfExists('honda_parts_assembly_images');
        Schema::dropIfExists('honda_parts_model_parts');
        Schema::dropIfExists('honda_parts_parts');
        Schema::dropIfExists('honda_parts_model_assemblies');
        Schema::dropIfExists('honda_parts_assemblies');
        Schema::dropIfExists('honda_parts_models');
        Schema::dropIfExists('honda_parts_catalogues');
    }
};
