<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;


class Entidade extends Model
{
    use SoftDeletes, BelongsToTenant; 

    protected $fillable = [
        'numero', 'is_cliente', 'is_fornecedor', 'nif', 'nome',
        'morada', 'codigo_postal', 'localidade', 'pais_id',
        'telefone', 'telemovel', 'website', 'email',
        'rgpd', 'observacoes', 'ativo', 'tenant_id',
    ];

    protected $casts = [
        'is_cliente'    => 'boolean',
        'is_fornecedor' => 'boolean',
        'rgpd'          => 'boolean',
        'ativo'         => 'boolean',

        // Dados pessoais
        'nif'           => 'encrypted',
        'email'         => 'encrypted',
        'telefone'      => 'encrypted',
        'telemovel'     => 'encrypted',
        'morada'        => 'encrypted',
        'codigo_postal' => 'encrypted',
        'observacoes'   => 'encrypted',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    public function contactos()
    {
        return $this->hasMany(Contacto::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Entidade $entidade) {
            $ultimo = static::withTrashed()->max('numero') ?? 0;
            $entidade->numero = $ultimo + 1;
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}