<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('size');
            $table->decimal('price', 10, 2);
            $table->decimal('gross_weight', 10, 2);
            $table->decimal('net_weight', 10, 2);
            $table->decimal('tare_weight', 10, 2);
            $table->decimal('standard_weight', 10, 2);
            $table->integer('free_quantity');
            $table->string('packaging');
            $table->string('box_dimensions');
            $table->string('box_packing');
            $table->boolean('in_stock')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_new')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
