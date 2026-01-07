<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('fungsios', function (Blueprint $table) {
        // Tambah kolom 'division' setelah 'email'
        // Kita buat nullable dulu biar aman jika ada data lama
        $table->string('division')->nullable()->after('email');
    });
}

public function down()
{
    Schema::table('fungsios', function (Blueprint $table) {
        $table->dropColumn('division');
    });
}
};
