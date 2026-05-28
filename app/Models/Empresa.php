<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $fillable = [
        'tenant_id',
        'nome',
        'morada',
        'codigo_postal',
        'localidade',
        'nif',
        'telefone',
        'email',
        'website',
        'logotipo',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}