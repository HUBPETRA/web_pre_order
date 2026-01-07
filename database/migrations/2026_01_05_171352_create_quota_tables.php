<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    // 1. Tabel Default Kuota per Divisi (Kamus Rumus)
    Schema::create('division_defaults', function (Blueprint $table) {
        $table->id();
        $table->string('division_name')->unique(); // Contoh: "Acara", "Humas"
        $table->integer('default_quota')->default(0);
        $table->timestamps();
    });

    // 2. Tabel Kuota Spesifik per Batch (Data Target Real)
    Schema::create('batch_quotas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
        $table->foreignId('fungsio_id')->constrained('fungsios')->onDelete('cascade');
        $table->integer('target_qty')->default(0);
        $table->timestamps();

        // Mencegah duplikasi data (1 orang cuma punya 1 target di 1 batch)
        $table->unique(['batch_id', 'fungsio_id']);
    });
}

public function down()
{
    Schema::dropIfExists('batch_quotas');
    Schema::dropIfExists('division_defaults');
}
};
