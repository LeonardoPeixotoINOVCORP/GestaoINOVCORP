<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class CalendarioEvento extends Model
{
    use BelongsToTenant;

    protected $table = 'calendario_eventos';

    protected $fillable = [
        'titulo', 'inicio', 'fim', 'duracao',
        'entidade_id', 'tipo_id', 'acao_id',
        'user_id', 'partilhado', 'descricao', 'estado',
    ];

    protected $casts = [
        'inicio'    => 'datetime',
        'fim'       => 'datetime',
        'partilhado' => 'boolean',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function tipo()
    {
        return $this->belongsTo(CalendarioTipo::class, 'tipo_id');
    }

    public function acao()
    {
        return $this->belongsTo(CalendarioAcao::class, 'acao_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}