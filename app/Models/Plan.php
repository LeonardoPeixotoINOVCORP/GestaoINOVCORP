<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'nome', 'slug', 'stripe_price_id', 'preco', 'intervalo',
        'max_utilizadores', 'max_clientes', 'max_artigos',
        'arquivo_digital', 'calendario', 'financeiro',
        'trial_dias', 'ativo',
    ];

    protected $casts = [
        'preco'           => 'decimal:2',
        'arquivo_digital' => 'boolean',
        'calendario'      => 'boolean',
        'financeiro'      => 'boolean',
        'ativo'           => 'boolean',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function isFree(): bool
    {
        return $this->preco == 0;
    }
}