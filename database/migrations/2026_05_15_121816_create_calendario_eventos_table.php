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
        Schema::create('calendario_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->dateTime('inicio');
            $table->dateTime('fim')->nullable();
            $table->integer('duracao')->nullable(); // minutos
            $table->foreignId('entidade_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->foreignId('tipo_id')->nullable()->constrained('calendario_tipos')->nullOnDelete();
            $table->foreignId('acao_id')->nullable()->constrained('calendario_acoes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('partilhado')->default(false);
            $table->text('descricao')->nullable();
            $table->string('estado')->default('pendente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendario_eventos');
    }
};
