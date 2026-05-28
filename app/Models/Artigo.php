<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Artigo extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'referencia', 'nome', 'descricao', 'preco',
        'iva', 'foto', 'observacoes', 'ativo', 'tenant_id',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'iva'   => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}