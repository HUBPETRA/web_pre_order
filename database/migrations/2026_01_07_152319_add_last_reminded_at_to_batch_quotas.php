<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('batch_quotas', function (Blueprint $table) {
        // Mencatat tanggal terakhir diingatkan
        $table->date('last_reminded_at')->nullable()->after('target_qty');
    });
}

public function down()
{
    Schema::table('batch_quotas', function (Blueprint $table) {
        $table->dropColumn('last_reminded_at');
    });
}
};
