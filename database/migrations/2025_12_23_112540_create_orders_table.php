<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Perintah ini akan MEMBUAT tabel baru (Schema::create)
        // Bukan meng-edit tabel (Schema::table)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone');
            
            // Kolom Data Pesanan & Bukti (Pengganti menu/qty lama)
            $table->text('order_details'); // Menyimpan JSON pesanan
            $table->string('payment_proof'); // Menyimpan nama file gambar
            
            $table->string('status')->default('Menunggu Verifikasi');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};