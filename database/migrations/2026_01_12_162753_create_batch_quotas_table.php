<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('batch_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->foreignId('fungsio_id')->constrained('fungsios')->onDelete('cascade');
            
            $table->integer('target_qty')->default(0);
            $table->date('last_reminded_at')->nullable();
            $table->boolean('is_fine_paid')->default(false);

            $table->timestamps();
            
            // Mencegah duplikasi (1 fungsio max 1 row per batch)
            $table->unique(['batch_id', 'fungsio_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('batch_quotas');
    }
};