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
        $tabelas = [
         'contactos', 'propostas', 'proposta_linhas',
            'encomendas', 'encomenda_linhas', 'faturas_fornecedor', 'arquivo_digital',
            'contas_bancarias', 'conta_corrente_clientes', 'calendario_eventos',
            'calendario_tipos', 'calendario_acoes', 'contactos_funcoes', 'paises',
        ];

        foreach ($tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) use ($tabela) {
                $table->foreignId('tenant_id')
                    ->after('id')
                    ->constrained('tenants')
                    ->cascadeOnDelete();
            });
        }
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('all_tables', function (Blueprint $table) {
            //
        });
    }
};
