<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_motos', function (Blueprint $table) {
            $table->id(); 
            $table->string('modelo');
            $table->string('nome');
            $table->string('nome_dono');
            $table->string('marca');
            $table->string('cor');
            $table->string('status');
            $table->string('observacao');
            $table->integer('quilometragem');
            $table->string('ano_fabricacao');
            $table->string('anexo');
            $table->integer('garantia');
            $table->string('token');
            $table->integer('deleted');
            $table->timestamps('created_at');
            $table->timestamps('updated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_motos');
    }
};
