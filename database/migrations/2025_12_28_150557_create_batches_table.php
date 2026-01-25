<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('whatsapp_link')->nullable();

            // Banner
            $table->string('banner_image')->nullable();
            
            // Kolom Tanggal
            $table->date('close_date')->nullable();
            $table->date('pickup_date')->nullable(); // Nullable saat pembuatan awal
            
            // Denda & Status
            $table->integer('fine_per_unit')->default(10000); 
            $table->boolean('is_active')->default(false);
            $table->boolean('is_reminder_sent')->default(false);

            //Info Finansial & Lokasi
            $table->string('pickup_location')->nullable(); 
            $table->decimal('starting_capital', 15, 2)->default(0); 
            $table->decimal('income', 15, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('batches');
    }
};