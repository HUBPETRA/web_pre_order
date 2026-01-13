<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fungsios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique(); // Wajib unik
            $table->string('division')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fungsios');
    }
};