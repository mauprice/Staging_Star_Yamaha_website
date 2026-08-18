<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yamaha_parts_products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('product_id')->primary();
            $table->string('model', 100);
            $table->string('common_name', 100)->nullable();
            $table->string('year', 10)->nullable()->index();
            $table->integer('type')->default(0)->index();
            $table->string('cat_number', 20)->nullable();
            $table->string('prefix', 50)->nullable();
            $table->text('serial')->nullable();
            $table->boolean('has_images')->default(false);
            $table->integer('admin_prod_id')->nullable();
            $table->timestamp('date_modified')->nullable();
            $table->timestamp('date_release')->nullable();
            $table->index(['model']);
        });

        Schema::create('yamaha_parts_contents', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('content_id')->primary();
            $table->integer('product_id')->index();
            $table->string('title', 100);
        });

        Schema::create('yamaha_parts_assemblies', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('assembly_id')->primary();
            $table->integer('content_id')->index();
            $table->string('title', 150);
            $table->integer('assembly_image_id')->nullable();
            $table->boolean('has_parts')->default(true);
        });

        Schema::create('yamaha_parts_images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('image_id')->primary();
            $table->string('filename', 20)->nullable();
            $table->string('format', 10)->default('webp');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->boolean('extracted')->default(false);
        });

        Schema::create('yamaha_parts_assembly_images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('assembly_image_id')->primary();
            $table->integer('assembly_id')->index();
            $table->integer('image_id')->index();
        });

        Schema::create('yamaha_parts_parts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('part_id')->primary();
            $table->integer('assembly_id')->index();
            $table->string('number', 30)->nullable()->index();
            $table->string('search_part_no', 40)->nullable()->index();
            $table->string('mangled_part_no', 40)->nullable();
            $table->string('desc', 100)->nullable();
            $table->string('remark', 150)->nullable();
            $table->integer('qty')->default(1);
            $table->integer('image_ref_no')->nullable()->index();
            $table->integer('img_x')->nullable();
            $table->integer('img_y')->nullable();
            $table->integer('parent_id')->nullable()->index();
            $table->boolean('has_child')->default(false);
        });

        Schema::create('yamaha_parts_model_images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('model_image_id')->primary();
            $table->integer('product_id')->index();
            $table->integer('image_id')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yamaha_parts_model_images');
        Schema::dropIfExists('yamaha_parts_parts');
        Schema::dropIfExists('yamaha_parts_assembly_images');
        Schema::dropIfExists('yamaha_parts_images');
        Schema::dropIfExists('yamaha_parts_assemblies');
        Schema::dropIfExists('yamaha_parts_contents');
        Schema::dropIfExists('yamaha_parts_products');
    }
};
