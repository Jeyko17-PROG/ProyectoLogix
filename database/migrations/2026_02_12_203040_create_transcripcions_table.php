<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transcripcions', function (Blueprint $table) {
            $table->id();
            $table->text('texto');
            $table->string('idioma')->default('es');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transcripcions');
    }
};

