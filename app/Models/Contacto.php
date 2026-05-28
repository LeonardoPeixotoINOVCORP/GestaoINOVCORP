<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Contacto extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'numero', 'entidade_id', 'nome', 'apelido',
        'funcao_id', 'telefone', 'telemovel', 'email',
        'rgpd', 'observacoes', 'ativo',
    ];

    protected $casts = [
        'rgpd'        => 'boolean',
        'ativo'       => 'boolean',

        // Dados pessoais
        'telefone'    => 'encrypted',
        'telemovel'   => 'encrypted',
        'email'       => 'encrypted',
        'observacoes' => 'encrypted',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function funcao()
    {
        return $this->belongsTo(ContactoFuncao::class, 'funcao_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Contacto $contacto) {
            $ultimo = static::withTrashed()->max('numero') ?? 0;
            $contacto->numero = $ultimo + 1;
        });
    }
}