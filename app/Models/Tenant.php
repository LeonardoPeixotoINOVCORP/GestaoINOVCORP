<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'slug', 'owner_id', 'plan_id',
        'stripe_customer_id', 'stripe_subscription_id',
        'subscription_status', 'trial_ends_at', 'subscription_ends_at',
        'logotipo', 'nif', 'morada', 'codigo_postal',
        'localidade', 'email', 'telefone', 'website', 'onboarding_completo',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'subscription_ends_at' => 'datetime',
        'checklist'            => 'array',
        'onboarding_completo'  => 'boolean',
    ];

    public function checklistCompleta(): array
    {
        return [
            'empresa'      => !empty($this->nome) && !empty($this->nif),
            'pais'         => \App\Models\Pais::count() > 0,
            'artigo'       => \App\Models\Artigo::count() > 0,
            'utilizador'   => $this->users()->count() > 1,
            'cliente'      => \App\Models\Entidade::where('is_cliente', true)->count() > 0,
        ];
    }

    public function percentagemChecklist(): int
    {
        $items = $this->checklistCompleta();
        $completos = count(array_filter($items));
        
        return (int) round(($completos / count($items)) * 100);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_users')
            ->withPivot('role', 'ativo')
            ->withTimestamps();
    }

    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

public function isActive(): bool
{
    if ($this->subscription_status === 'active') return true;

    if ($this->subscription_status === 'trial' && $this->trial_ends_at?->isFuture()) return true;
    
    if ($this->subscription_status === 'canceled' && $this->subscription_ends_at?->isFuture()) return true;
    
    return false;
}

    public function trialDaysLeft(): int
    {
        if (!$this->trial_ends_at) { return 0; }
        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function slugify(string $nome): string
    {
        return \Str::slug($nome);
    }

    public function canCreateUtilizadores(): bool
    {
        $max = $this->plan?->max_utilizadores;

        if ($max === null) return true;

        return $this->users()->count() < $max;
    }

    public function canCreateClientes(): bool
    {
        $max = $this->plan?->max_clientes;

        if ($max === null) {
            return true;
        }

        $total = $this->entidades()
            ->where('is_cliente', true)
            ->count();

        return $total < $max;
    }

    public function canCreateArtigos(): bool
    {
        $max = $this->plan?->max_artigos;

        if ($max === null) return true;

        return $this->artigos()->count() < $max;
    }

    public function canUseArquivoDigital(): bool
    {
        return (bool) $this->plan?->arquivo_digital;
    }

    public function canUseCalendario(): bool
    {
        return (bool) $this->plan?->calendario;
    }

    public function canUseFinanceiro(): bool
    {
        return (bool) $this->plan?->financeiro;
    }

    public function entidades()
    {
        return $this->hasMany(Entidade::class);
    }

    public function artigos()
    {
        return $this->hasMany(Artigo::class);
    }

    public function empresa()
    {
        return $this->hasOne(Empresa::class);
    }
}