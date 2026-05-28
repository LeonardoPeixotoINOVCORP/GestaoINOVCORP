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
        Schema::create('encomendas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->unique();
            $table->foreignId('entidade_id')->constrained('entidades');
            $table->foreignId('proposta_id')->nullable()->constrained('propostas')->nullOnDelete();
            $table->date('data_encomenda')->nullable();
            $table->enum('tipo', ['cliente', 'fornecedor'])->default('cliente');
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
        Schema::dropIfExists('encomendas');
    }
};
