<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class ArquivoDigital extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'arquivo_digital';

    protected $fillable = [
        'nome', 'ficheiro', 'tipo_mime',
        'tamanho', 'entidade_id', 'observacoes', 'user_id',
    ];

    protected $casts = [

        // Informação privada
        'ficheiro'       => 'encrypted',
        'observacoes'    => 'encrypted',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}