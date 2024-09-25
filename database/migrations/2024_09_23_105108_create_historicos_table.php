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
        Schema::create('historicos', function (Blueprint $table) {
            $table->id();
            $table->foreign('historico_carro_id')->references('id')->on('config_carros');
            $table->foreign('historico_moto_id')->references('id')->on('config_motos'); 
            $table->date('data');
            $table->string('observacoes');
            $table->enum('status', ['Em manutenção', 'Aguardando peças', 'Concluído']);
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
        Schema::dropIfExists('historicos');
    }
};
