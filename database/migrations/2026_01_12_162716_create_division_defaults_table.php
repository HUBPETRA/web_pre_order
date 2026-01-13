<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('division_defaults', function (Blueprint $table) {
            $table->id();
            $table->string('division_name')->unique();
            $table->integer('default_quota')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('division_defaults');
    }
};