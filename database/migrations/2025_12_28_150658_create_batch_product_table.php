<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('batch_product', function (Blueprint $table) {
            $table->id();
            // Foreign Key Batches
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            // Foreign Key Products 
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            $table->integer('price');
            $table->integer('stock');
            $table->integer('sold')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_product');
    }
};
