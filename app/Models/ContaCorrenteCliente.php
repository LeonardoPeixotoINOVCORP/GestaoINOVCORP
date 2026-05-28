<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class ContaCorrenteCliente extends Model
{

    use BelongsToTenant;

    protected $fillable = [
        'entidade_id', 'data_movimento', 'descricao',
        'valor', 'tipo', 'referencia',
    ];

    protected $casts = [
        'data_movimento' => 'date',
        'valor'          => 'decimal:2',

        // Dados financeiros
        'descricao'      => 'encrypted',
        'referencia'     => 'encrypted',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }
}