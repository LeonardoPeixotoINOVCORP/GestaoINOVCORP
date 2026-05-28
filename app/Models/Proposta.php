<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Proposta extends Model
{

    use BelongsToTenant;

    protected $fillable = [
        'entidade_id', 'data_proposta', 'validade',
        'estado', 'observacoes',
    ];

    protected $casts = [
        'data_proposta' => 'date',
        'validade'      => 'date',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function linhas()
    {
        return $this->hasMany(PropostaLinha::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->linhas()
            ->get()
            ->sum(function ($linha) {
                return $linha->quantidade
                    * $linha->preco_venda
                    * (1 + $linha->iva / 100);
            });
    }

    protected static function booted(): void
    {
        static::creating(function (Proposta $proposta) {
            $ultimo = static::withTrashed()->max('numero') ?? 0;
            $proposta->numero = $ultimo + 1;
        });
    }
}