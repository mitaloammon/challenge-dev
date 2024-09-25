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
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->date('data_agendamento');
            $table->dateTime('hora_agendamento');
            $table->enum('status', ['Confirmado', 'Cancelado', 'Concluído']);
            $table->foreign('agendamento_carro_id')->references('id')->on('config_carros');
            $table->foreign('agendamento_moto_id')->references('id')->on('config_motos'); 
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
        Schema::dropIfExists('agendamentos');
    }
};
