<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->string('customer_email')->nullable()->after('customer_name');
        $table->foreignId('fungsio_id')->nullable()->constrained('fungsios')->onDelete('set null')->after('customer_phone');
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropForeign(['fungsio_id']);
        $table->dropColumn(['fungsio_id', 'customer_email']);
    });
}
};
