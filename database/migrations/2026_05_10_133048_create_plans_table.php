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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('stripe_price_id')->nullable();
            $table->decimal('preco', 10, 2)->default(0);
            $table->string('intervalo')->default('month'); // month, year
            $table->integer('max_utilizadores')->default(5);
            $table->integer('max_clientes')->default(100);
            $table->integer('max_artigos')->default(100);
            $table->boolean('arquivo_digital')->default(true);
            $table->boolean('calendario')->default(true);
            $table->boolean('financeiro')->default(true);
            $table->integer('trial_dias')->default(14);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
