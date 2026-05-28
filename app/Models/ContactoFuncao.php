<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ContactoFuncao extends Model
{

    use BelongsToTenant;

    protected $table = 'contactos_funcoes';

    protected $fillable = ['nome', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];
}