<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('batches', function (Blueprint $table) {
        // Kolom template pesan
        $table->text('mail_message')->nullable()->after('whatsapp_link');

        // Kolom penanda status pengiriman (biar gak double kirim)
        $table->boolean('is_reminder_sent')->default(false)->after('is_active');
    });
}

public function down()
{
    Schema::table('batches', function (Blueprint $table) {
        $table->dropColumn(['mail_message', 'is_reminder_sent']);
    });
}
};
