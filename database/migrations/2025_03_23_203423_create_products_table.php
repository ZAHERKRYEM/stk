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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique(); 
            $table->json('name_translations')->nullable(); 
            $table->json('description_translations')->nullable(); 
            $table->decimal('price', 8, 2); 
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); 
            $table->string('image_url')->nullable();
            $table->json('gallery')->nullable();
            $table->json('sizes')->nullable(); 

          
            $table->string('country_of_origin')->nullable(); 
            $table->string('material_property')->nullable();
            $table->string('product_category')->nullable(); 
            $table->decimal('gross_weight', 8, 2)->nullable(); 
            $table->decimal('net_weight', 8, 2)->nullable(); 
            $table->decimal('tare_weight', 8, 2)->nullable(); 
            $table->decimal('standard_weight', 8, 2)->nullable(); 
            $table->integer('free_quantity')->nullable(); 
            $table->string('weight_unit')->nullable(); 
            $table->string('packaging')->nullable(); 
            $table->string('supplier_name')->nullable(); 

           
            $table->decimal('box_gross_weight', 8, 2)->nullable(); 
            $table->string('box_dimensions')->nullable();
            $table->string('box_packing')->nullable(); 
            $table->boolean('in_stock')->default(true); 
            $table->boolean('is_hidden')->default(false); 
            $table->boolean('is_new')->default(true); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
