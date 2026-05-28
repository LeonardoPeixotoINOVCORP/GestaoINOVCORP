<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;


class CalendarioTipo extends Model
{

    use BelongsToTenant;

    protected $table = 'calendario_tipos';

    protected $fillable = ['nome', 'cor', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];
}