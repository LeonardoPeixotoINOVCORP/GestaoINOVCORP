<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class PropostaLinha extends Model
{

    use BelongsToTenant;

    protected $fillable = [
        'proposta_id', 'artigo_id', 'fornecedor_id',
        'quantidade', 'preco_venda', 'preco_custo', 'iva',
    ];

    protected $casts = [
        'preco_venda' => 'decimal:2',
        'preco_custo' => 'decimal:2',
        'iva'         => 'decimal:2',
    ];

    public function artigo()
    {
        return $this->belongsTo(Artigo::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Entidade::class, 'fornecedor_id');
    }
}