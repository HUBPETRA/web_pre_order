<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Relasi Batch: Hapus order jika batch dihapus
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            
            // Relasi Fungsio: Set null jika fungsio dihapus (agar data penjualan tetap ada)
            $table->foreignId('fungsio_id')->nullable()->constrained('fungsios')->onDelete('set null');

            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable(); // Ditambah nullable untuk jaga-jaga
            
            $table->string('payment_proof'); 
            $table->integer('total_amount')->default(0); // [PENTING] Kolom ini tadi kurang
            
            $table->string('status')->default('Menunggu Verifikasi');
            $table->boolean('is_received')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};