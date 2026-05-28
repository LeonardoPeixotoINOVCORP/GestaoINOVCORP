<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class FaturaFornecedor extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'faturas_fornecedor';

    protected $fillable = [
        'numero', 'data_fatura', 'data_vencimento',
        'fornecedor_id', 'encomenda_id', 'valor_total',
        'documento', 'comprovativo', 'estado',
    ];

    protected $casts = [
        'data_fatura'     => 'date',
        'data_vencimento' => 'date',
        'valor_total'     => 'decimal:2',

        // Informação privada
        'documento'       => 'encrypted',
        'comprovativo'    => 'encrypted',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Entidade::class, 'fornecedor_id');
    }

    public function encomenda()
    {
        return $this->belongsTo(Encomenda::class);
    }

    protected static function booted(): void
    {
        static::creating(function (FaturaFornecedor $fatura) {
            $ultimo = static::withTrashed()->max('numero') ?? 0;
            $fatura->numero = $ultimo + 1;
        });
    }
}