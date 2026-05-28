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
        Schema::create('faturas_fornecedor', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->unique();
            $table->date('data_fatura');
            $table->date('data_vencimento')->nullable();
            $table->foreignId('fornecedor_id')->constrained('entidades');
            $table->foreignId('encomenda_id')->nullable()->constrained('encomendas')->nullOnDelete();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('documento')->nullable();
            $table->string('comprovativo')->nullable();
            $table->enum('estado', ['pendente', 'paga'])->default('pendente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faturas_fornecedor');
    }
};
