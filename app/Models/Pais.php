<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Pais extends Model
{

    use BelongsToTenant;

    protected $table = 'paises';
    
    protected $fillable = ['nome', 'codigo', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];
}