<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('propostas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->unique();
            $table->foreignId('entidade_id')->constrained('entidades');
            $table->date('data_proposta')->nullable();
            $table->date('validade')->nullable();
            $table->enum('estado', ['rascunho', 'fechado'])->default('rascunho');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propostas');
    }
};
