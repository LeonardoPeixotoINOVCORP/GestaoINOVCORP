<?php

use App\Http\Controllers\ArquivoDigitalController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\ContaBancariaController;
use App\Http\Controllers\ContaCorrenteClienteController;
use App\Http\Controllers\CalendarioTipoController;
use App\Http\Controllers\CalendarioAcaoController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EncomendaController;
use App\Http\Controllers\EntidadeController;
use App\Http\Controllers\ArtigoController;
use App\Http\Controllers\FaturaFornecedorController;
use App\Http\Controllers\ContactoFuncaoController;
use App\Http\Controllers\GestaoAcessos\PermissaoController;
use App\Http\Controllers\GestaoAcessos\UtilizadorController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\PropostaController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ConviteController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

/* Rotas autenticadas sem tenant */
Route::middleware(['auth', 'verified'])->group(function () {

    /* Tenants */
    Route::get('tenants/select', [TenantController::class, 'select'])->name('tenant.select');
    Route::post('tenants/switch/{tenant}', [TenantController::class, 'switch'])->name('tenant.switch');
    

});

Route::middleware(['auth', 'verified', 'can.create.tenants'])->group(function () {
    Route::get('tenants/create', [TenantController::class, 'create'])->name('tenant.create');
    Route::post('tenants', [TenantController::class, 'store'])->name('tenant.store');
});

/* Rotas autenticadas com tenant */
Route::middleware(['auth', 'verified', 'tenant'])->group(function () {

    /* Onboarding */
    Route::get('onboarding', [OnboardingController::class, 'index'])
        ->name('onboarding.wizard');
    Route::post('onboarding/passo/{passo}', [OnboardingController::class, 'store'])
        ->name('onboarding.store');
    Route::post('onboarding/concluir', [OnboardingController::class, 'complete'])
        ->name('onboarding.complete');
    Route::post('onboarding/back', [OnboardingController::class, 'back'])
        ->name('onboarding.back');

    /* Convites */
    Route::post('convites', [ConviteController::class, 'store'])->name('convites.store');
    Route::delete('convites/{user}', [ConviteController::class, 'destroy'])->name('convites.destroy');

    /* Billing expirado */
    Route::get('billing/expirado', [BillingController::class, 'expirado'])
        ->name('billing.expirado');
});


/* Rotas autenticadas com tenant e onboarding */
Route::middleware(['auth', 'verified', 'tenant','permissions.team','onboarding'])->group(function () {

    /* Dashboard */

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /* Billing */

    Route::prefix('billing')->name('billing.')->group(function () {

        Route::get('/', [BillingController::class, 'index'])
            ->name('index');

        Route::get('planos', [BillingController::class, 'planos'])
            ->name('planos');

        Route::post('subscrever/{plano}', [BillingController::class, 'subscrever'])
            ->name('subscrever');

        Route::post('cancelar', [BillingController::class, 'cancelar'])
            ->name('cancelar');

        Route::get('portal', [BillingController::class, 'portal'])
            ->name('portal');

        Route::get('logs', [BillingController::class, 'logs'])->name('logs');
    });

    /* Clientes */

    Route::get('clientes', [EntidadeController::class, 'index'])
        ->middleware('permission:read_clientes')
        ->defaults('tipo', 'cliente')
        ->name('clientes.index');

    Route::get('clientes/criar', [EntidadeController::class, 'create'])
        ->middleware('permission:create_clientes')
        ->defaults('tipo', 'cliente')
        ->name('clientes.create');

    /* Fornecedores */

    Route::get('fornecedores', [EntidadeController::class, 'index'])
        ->middleware('permission:read_fornecedores')
        ->defaults('tipo', 'fornecedor')
        ->name('fornecedores.index');

    Route::get('fornecedores/criar', [EntidadeController::class, 'create'])
        ->middleware('permission:create_fornecedores')
        ->defaults('tipo', 'fornecedor')
        ->name('fornecedores.create');

    /* Entidades */

    Route::post('entidades', [EntidadeController::class, 'store'])
        ->middleware('permission:create_clientes|create_fornecedores')
        ->name('entidades.store');

    Route::get('entidades/{entidade}/edit', [EntidadeController::class, 'edit'])
        ->middleware('permission:update_clientes|update_fornecedores')
        ->name('entidades.edit');

    Route::put('entidades/{entidade}', [EntidadeController::class, 'update'])
        ->middleware('permission:update_clientes|update_fornecedores')
        ->name('entidades.update');

    Route::delete('entidades/{entidade}', [EntidadeController::class, 'destroy'])
        ->middleware('permission:delete_clientes|delete_fornecedores')
        ->name('entidades.destroy');

    /* Contactos */

    Route::resource('contactos', ContactoController::class)
        ->except(['show'])
        ->middleware([
            'index' => 'permission:read_contactos',
            'create' => 'permission:create_contactos',
            'store' => 'permission:create_contactos',
            'edit' => 'permission:update_contactos',
            'update' => 'permission:update_contactos',
            'destroy' => 'permission:delete_contactos',
        ]);

    /* Propostas */

    Route::resource('propostas', PropostaController::class)
        ->except(['show'])
        ->middleware([
            'index' => 'permission:read_propostas',
            'create' => 'permission:create_propostas',
            'store' => 'permission:create_propostas',
            'edit' => 'permission:update_propostas',
            'update' => 'permission:update_propostas',
            'destroy' => 'permission:delete_propostas',
        ]);

    Route::post('propostas/{proposta}/converter', [PropostaController::class, 'converterEmEncomenda'])
        ->middleware('permission:update_propostas')
        ->name('propostas.converter');

    Route::get('propostas/{proposta}/pdf', [PropostaController::class, 'pdf'])
        ->middleware('permission:read_propostas')
        ->name('propostas.pdf');

    /* Encomendas cliente */

    Route::get('encomendas', [EncomendaController::class, 'index'])
        ->middleware('permission:read_encomendas')
        ->defaults('tipo', 'cliente')
        ->name('encomendas.index');

    Route::get('encomendas/criar', [EncomendaController::class, 'create'])
        ->middleware('permission:create_encomendas')
        ->defaults('tipo', 'cliente')
        ->name('encomendas.create');

    /* Encomendas fornecedor */

    Route::get('encomendas-fornecedor', [EncomendaController::class, 'index'])
        ->middleware('permission:read_encomendas-fornecedor')
        ->defaults('tipo', 'fornecedor')
        ->name('encomendas.fornecedor.index');

    Route::get('encomendas-fornecedor/criar', [EncomendaController::class, 'create'])
        ->middleware('permission:create_encomendas-fornecedor')
        ->defaults('tipo', 'fornecedor')
        ->name('encomendas.fornecedor.create');

    /* Encomendas */

    Route::post('encomendas', [EncomendaController::class, 'store'])
        ->middleware('permission:create_encomendas|create_encomendas-fornecedor')
        ->name('encomendas.store');

    Route::get('encomendas/{encomenda}/editar', [EncomendaController::class, 'edit'])
        ->middleware('permission:update_encomendas|update_encomendas-fornecedor')
        ->name('encomendas.edit');

    Route::put('encomendas/{encomenda}', [EncomendaController::class, 'update'])
        ->middleware('permission:update_encomendas|update_encomendas-fornecedor')
        ->name('encomendas.update');

    Route::delete('encomendas/{encomenda}', [EncomendaController::class, 'destroy'])
        ->middleware('permission:delete_encomendas|delete_encomendas-fornecedor')
        ->name('encomendas.destroy');

    Route::post('encomendas/{encomenda}/converter-fornecedor', [EncomendaController::class, 'converterEmEncomendasFornecedor'])
        ->middleware('permission:update_encomendas')
        ->name('encomendas.converter-fornecedor');

    Route::get('encomendas/{encomenda}/pdf', [EncomendaController::class, 'pdf'])
        ->middleware('permission:read_encomendas|read_encomendas-fornecedor')
        ->name('encomendas.pdf');

    /*  Financeiro */

    Route::resource('faturas-fornecedor', FaturaFornecedorController::class)
        ->except(['show'])
        ->middleware('plan.feature:financeiro')
        ->middleware([
            'index'   => 'permission:read_faturas-fornecedor',
            'create'  => 'permission:create_faturas-fornecedor',
            'store'   => 'permission:create_faturas-fornecedor',
            'edit'    => 'permission:update_faturas-fornecedor',
            'update'  => 'permission:update_faturas-fornecedor',
            'destroy' => 'permission:delete_faturas-fornecedor',
        ]);

    Route::get('faturas-fornecedor/{faturasFornecedor}/download', [FaturaFornecedorController::class, 'download'])
        ->middleware(['permission:read_faturas-fornecedor', 'plan.feature:financeiro'])
        ->name('faturas-fornecedor.download');

    Route::post('faturas-fornecedor/{faturasFornecedor}/comprovativo', [FaturaFornecedorController::class, 'enviarComprovativo'])
        ->middleware(['permission:update_faturas-fornecedor', 'plan.feature:financeiro'])
        ->name('faturas-fornecedor.comprovativo');

    /* Arquivo digital */

    Route::get('arquivo', [ArquivoDigitalController::class, 'index'])
    ->middleware(['permission:read_arquivo', 'plan.feature:arquivo'])
    ->name('arquivo.index');

    Route::post('arquivo', [ArquivoDigitalController::class, 'store'])
        ->middleware(['permission:create_arquivo', 'plan.feature:arquivo'])
        ->name('arquivo.store');

    Route::get('arquivo/{arquivoDigital}/download', [ArquivoDigitalController::class, 'download'])
        ->middleware(['permission:read_arquivo', 'plan.feature:arquivo'])
        ->name('arquivo.download');

    Route::delete('arquivo/{arquivoDigital}', [ArquivoDigitalController::class, 'destroy'])
        ->middleware(['permission:delete_arquivo', 'plan.feature:arquivo'])
        ->name('arquivo.destroy');

    /* Contas bancárias */

    Route::resource('contas-bancarias', ContaBancariaController::class)
        ->except(['show'])
        ->middleware('plan.feature:financeiro')
        ->middleware([
            'index'   => 'permission:read_contas-bancarias',
            'create'  => 'permission:create_contas-bancarias',
            'store'   => 'permission:create_contas-bancarias',
            'edit'    => 'permission:update_contas-bancarias',
            'update'  => 'permission:update_contas-bancarias',
            'destroy' => 'permission:delete_contas-bancarias',
        ]);

  
    /* Conta corrente */

    Route::resource('conta-corrente', ContaCorrenteClienteController::class)
        ->parameters([
            'conta-corrente' => 'contaCorrente',
        ])
        ->except(['show'])
        ->middleware('plan.feature:financeiro')
        ->middleware([
            'index' => 'permission:read_conta-corrente',
            'create' => 'permission:create_conta-corrente',
            'store' => 'permission:create_conta-corrente',
            'edit' => 'permission:update_conta-corrente',
            'update' => 'permission:update_conta-corrente',
            'destroy' => 'permission:delete_conta-corrente',
        ]);

    /* Calendário */

    Route::get('calendario', [CalendarioController::class, 'index'])
        ->middleware(['permission:read_calendario', 'plan.feature:calendario'])
        ->name('calendario.index');

    Route::get('calendario/eventos', [CalendarioController::class, 'eventos'])
        ->middleware(['permission:read_calendario', 'plan.feature:calendario'])
        ->name('calendario.eventos');

    Route::post('calendario', [CalendarioController::class, 'store'])
        ->middleware(['permission:create_calendario', 'plan.feature:calendario'])
        ->name('calendario.store');

    Route::put('calendario/{evento}', [CalendarioController::class, 'update'])
        ->middleware(['permission:update_calendario', 'plan.feature:calendario'])
        ->name('calendario.update');

    Route::delete('calendario/{evento}', [CalendarioController::class, 'destroy'])
        ->middleware(['permission:delete_calendario', 'plan.feature:calendario'])
        ->name('calendario.destroy');

    /* Configurações */

    Route::resource('configuracoes/artigos', ArtigoController::class)
        ->names('configuracoes.artigos')
        ->except(['show']);

    Route::resource('configuracoes/paises', PaisController::class)
        ->names('configuracoes.paises')
        ->except(['show']);

    Route::resource('configuracoes/contactos-funcoes', ContactoFuncaoController::class)
        ->names('configuracoes.contactos-funcoes')
        ->except(['show']);

    Route::get('configuracoes/empresa', [EmpresaController::class, 'edit'])
        ->middleware('permission:read_configuracoes')
        ->name('empresa.edit');

    Route::put('configuracoes/empresa', [EmpresaController::class, 'update'])
        ->middleware('permission:update_configuracoes')
        ->name('empresa.update');

    Route::resource('configuracoes/calendario-tipos', CalendarioTipoController::class)
        ->names('configuracoes.calendario-tipos')
        ->except(['show']);

    Route::resource('configuracoes/calendario-acoes', CalendarioAcaoController::class)
        ->names('configuracoes.calendario-acoes')
        ->except(['show']);
        
    /* Utilizadores */

    Route::resource(
        'gestao-acessos/utilizadores',
        UtilizadorController::class
    )
        ->names('gestao-acessos.utilizadores')
        ->parameters([
            'utilizadores' => 'utilizador',
        ])
        ->except(['show'])
        ->middleware([
            'index' => 'permission:read_utilizadores',
            'create' => 'permission:create_utilizadores',
            'store' => 'permission:create_utilizadores',
            'edit' => 'permission:update_utilizadores',
            'update' => 'permission:update_utilizadores',
            'destroy' => 'permission:delete_utilizadores',
        ]);

    /* Permissões */

    Route::resource(
        'gestao-acessos/permissoes',
        PermissaoController::class
    )
        ->names('gestao-acessos.permissoes')
        ->parameters([
            'permissoes' => 'permissao',
        ])
        ->except(['show'])
        ->middleware([
            'index' => 'permission:read_permissoes',
            'create' => 'permission:create_permissoes',
            'store' => 'permission:create_permissoes',
            'edit' => 'permission:update_permissoes',
            'update' => 'permission:update_permissoes',
            'destroy' => 'permission:delete_permissoes',
        ]);

    /* Logs */

    Route::get('logs', [LogController::class, 'index'])
        ->middleware('permission:read_logs')
        ->name('logs.index');

    Route::get('billings', [BillingController::class, 'index'])
        ->name('billings.index');
    
    /* Notificações */
    Route::post('notificacoes/ler-todas', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->middleware(['auth'])->name('notificacoes.lerTodas');

});

/* Stripe webhook */

Route::post('billing/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('billing.webhook');
    
require __DIR__ . '/settings.php';