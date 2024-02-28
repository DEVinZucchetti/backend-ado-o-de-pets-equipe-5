<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adoptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pet_id');
            $table->string('name');
            $table->string('email');
            $table->string('cpf');
            $table->string('contact', 20);
            $table->string('observations');
            $table->enum('status',['PENDENTE','NEGADO','APROVADO']);
            $table->foreign('pet_id')->references('id')->on('pets');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('adoptions');
    }
};