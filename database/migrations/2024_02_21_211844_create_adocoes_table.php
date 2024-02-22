<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdocoesTable extends Migration
{
    public function up()
    {
        Schema::create('adocoes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('contact');
            $table->text('observations');
            $table->enum('status', ['PENDENTE', 'NEGADO', 'APROVADO']);
            $table->unsignedBigInteger('pet_id');
            $table->timestamps();

            $table->foreign('pet_id')->references('id')->on('pets');
        });
    }

    public function down()
    {
        Schema::dropIfExists('adocoes');
    }
}

