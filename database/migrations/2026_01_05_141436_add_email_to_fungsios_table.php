<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('fungsios', function (Blueprint $table) {
        // Tambah kolom email setelah nama, wajib unique (tidak boleh kembar)
        $table->string('email')->unique()->after('name');
    });
}

public function down()
{
    Schema::table('fungsios', function (Blueprint $table) {
        $table->dropColumn('email');
    });
}
};
