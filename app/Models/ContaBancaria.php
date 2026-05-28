<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class ContaBancaria extends Model
{

    use BelongsToTenant;
    
    protected $table = 'contas_bancarias';

    protected $fillable = [
        'banco', 'iban', 'swift', 'titular', 'ativo',
    ];

    protected $casts = [
        'ativo'   => 'boolean',

        // Dados bancários
        'iban'    => 'encrypted',
        'swift'   => 'encrypted',
        'titular' => 'encrypted',
    ];
}