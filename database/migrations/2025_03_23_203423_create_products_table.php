<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->json('name_translations');
            $table->json('description_translations')->nullable();
            $table->foreignId('category_id')->constrained();
            $table->string('country_of_origin');
            $table->string('material_property');
            $table->string('product_category');
            $table->string('weight_unit');
            $table->string('barcode')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};