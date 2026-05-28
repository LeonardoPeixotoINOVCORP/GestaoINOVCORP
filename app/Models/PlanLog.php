<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanLog extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'plan_anterior_id',
        'plan_novo_id', 'acao', 'valor_pago', 'notas',
    ];

    protected $casts = [
        'valor_pago' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function planAnterior()
    {
        return $this->belongsTo(Plan::class, 'plan_anterior_id');
    }

    public function planNovo()
    {
        return $this->belongsTo(Plan::class, 'plan_novo_id');
    }
}