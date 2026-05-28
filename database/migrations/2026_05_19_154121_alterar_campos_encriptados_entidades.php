<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entidades', function (Blueprint $table) {

            // remover unique/index
            $table->dropUnique(['nif']);

            // alterar tipos
            $table->text('nif')->nullable()->change();
            $table->text('email')->nullable()->change();
            $table->text('telefone')->nullable()->change();
            $table->text('telemovel')->nullable()->change();
            $table->text('morada')->nullable()->change();
            $table->text('codigo_postal')->nullable()->change();
            $table->text('observacoes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('entidades', function (Blueprint $table) {

            $table->string('nif')->nullable()->unique()->change();
            $table->string('email')->nullable()->change();
            $table->string('telefone')->nullable()->change();
            $table->string('telemovel')->nullable()->change();
            $table->string('morada')->nullable()->change();
            $table->string('codigo_postal')->nullable()->change();
            $table->text('observacoes')->nullable()->change();
        });
    }
};