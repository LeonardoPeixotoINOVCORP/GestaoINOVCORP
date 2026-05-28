<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contactos', function (Blueprint $table) {

            $table->text('telefone')->nullable()->change();
            $table->text('telemovel')->nullable()->change();
            $table->text('email')->nullable()->change();
            $table->text('observacoes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contactos', function (Blueprint $table) {

            $table->string('telefone')->nullable()->change();
            $table->string('telemovel')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->text('observacoes')->nullable()->change();
        });
    }
};