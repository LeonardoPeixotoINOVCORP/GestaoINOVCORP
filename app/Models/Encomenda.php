<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Encomenda extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'entidade_id', 'proposta_id', 'data_encomenda',
        'tipo', 'estado', 'observacoes',
    ];

    protected $casts = [
        'data_encomenda' => 'date',

        // Informação interna
        'observacoes'    => 'encrypted',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function proposta()
    {
        return $this->belongsTo(Proposta::class);
    }

    public function linhas()
    {
        return $this->hasMany(EncomendaLinha::class);
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
        static::creating(function (Encomenda $encomenda) {

            $ultimo = static::withTrashed()
                ->max('numero') ?? 0;

            $encomenda->numero = $ultimo + 1;
        });
    }
}