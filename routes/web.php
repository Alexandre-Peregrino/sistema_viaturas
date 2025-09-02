<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterLdapController;

// Admin controllers (escopo global)
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\ManutencaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\MarcaModeloController;
use App\Http\Controllers\RelatorioUnificadoController;
use App\Http\Controllers\SisgpController;
use App\Http\Controllers\Admin\VeiculoSyncController;

// P4 controllers (escopo OPM do usuário)
use App\Http\Controllers\P4\ViaturaController as P4ViaturaController;
use App\Http\Controllers\P4\RadioController as P4RadioController;
use App\Http\Controllers\P4\ManutencaoController as P4ManutencaoController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/home', fn() => view('home'))->name('home');
Route::get('/', fn() => redirect()->route('home'));
Route::get('/funcionalidades', fn() => view('funcionalidades'))->name('funcionalidades');

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Rotas ADMIN (can:isAdmin)
    |--------------------------------------------------------------------------
    |
    | Admin mantém os controllers “globais”, sem filtro por OPM.
    */
    Route::middleware('can:isAdmin')->group(function () {
        // Usuários
        Route::resource('/admin/usuarios', UsuarioController::class)
            ->except(['show', 'create', 'store']) // impede criação manual
            ->names('admin.usuarios');


        // Viaturas (CRUD + extras)
        Route::resource('/admin/viaturas', VeiculoController::class)
            ->except(['show'])
            ->names('admin.viaturas');

        Route::get('/admin/viaturas/relatorio', [VeiculoController::class, 'relatorio'])
            ->name('admin.viaturas.relatorio');

        Route::get('/admin/viaturas/por-opm', [VeiculoController::class, 'porOpm'])
            ->name('admin.viaturas.por_opm');

        // ROTA: consulta (pré-visualização) e sincronização
        Route::post('/admin/viaturas/rota/probe', [VeiculoSyncController::class, 'probe'])
            ->name('admin.viaturas.rota.probe');

        // Manutenções
        Route::resource('/admin/manutencoes', ManutencaoController::class)
            ->except(['show'])
            ->names('admin.manutencoes');

        // Rádios
        Route::resource('/admin/radios', RadioController::class)
            ->except(['show'])
            ->names('admin.radios');

        Route::get('/admin/relatorios/radios', [RelatorioController::class, 'radiosResultado'])
            ->name('admin.relatorios.radios');

        // Relatórios (controlador antigo — compatibilidade)
        Route::get('/admin/relatorios', [RelatorioController::class, 'index'])
            ->name('relatorios.index');

        Route::get('/admin/relatorios/filtrar', [RelatorioController::class, 'filtrar'])
            ->name('relatorios.filtrar');

        Route::get('/admin/relatorios/geral', [RelatorioController::class, 'geral'])
            ->name('admin.relatorios.geral');

        Route::get('/admin/relatorios/viaturas', [RelatorioController::class, 'viaturas'])
            ->name('admin.relatorios.viaturas');

        Route::get('/admin/relatorios/viaturas/filtros', [RelatorioController::class, 'viaturasFiltros'])
            ->name('admin.relatorios.viaturas.filtros');

        Route::get('/admin/relatorios/manutencoes', [RelatorioController::class, 'manutencoesResultado'])
            ->name('admin.relatorios.manutencoes');

        // Filtros adicionais (mantidos)
        Route::get('/admin/relatorios/usuarios/filtros', [RelatorioController::class, 'usuariosFiltros'])
            ->name('admin.relatorios.usuarios.filtros');

        Route::get('/admin/relatorios/radios/filtros', [RelatorioController::class, 'radiosFiltros'])
            ->name('admin.relatorios.radios.filtros');

        Route::get('/admin/relatorios/manutencoes/filtros', [RelatorioController::class, 'manutencoesFiltros'])
            ->name('admin.relatorios.manutencoes.filtros');

        // Marcas/Modelos
        Route::resource('/admin/marcas-modelos', MarcaModeloController::class)
            ->names('admin.marcas_modelos');

        // Usuários — resultado relatório
        Route::get('/admin/relatorios/usuarios/resultado', [RelatorioController::class, 'usuariosResultado'])
            ->name('admin.relatorios.usuarios.resultado');

        // Relatório Unificado
        Route::get('/admin/relatorios/filtros-unificados', [RelatorioUnificadoController::class, 'filtros'])
            ->name('admin.relatorios.filtros_unificados');

        Route::get('/admin/relatorios/resultado-unificado', [RelatorioUnificadoController::class, 'resultado'])
            ->name('admin.relatorios.resultado_unificado');

        Route::get('/admin/viaturas/por-cpr', [RelatorioController::class, 'viaturasPorCpr'])
            ->name('admin.viaturas.por_cpr');

        /* ------------------------------------------------------------------
         | Relatórios RotaWeb (TELAS internas) – NOVO
         | Estas rotas apresentam formulários e tabelas consumindo o serviço
         | App\Services\RotawebClient diretamente no backend.
         ------------------------------------------------------------------ */
        Route::get('/admin/relatorios/rotaweb/efetivo-previsto/filtros', [RelatorioController::class, 'rotawebEfetivoPrevistoForm'])
            ->name('admin.relatorios.rotaweb.efetivo_previsto.filtros');

        Route::get('/admin/relatorios/rotaweb/efetivo-previsto/resultado', [RelatorioController::class, 'rotawebEfetivoPrevistoResultado'])
            ->name('admin.relatorios.rotaweb.efetivo_previsto.resultado');

        Route::get('/admin/relatorios/rotaweb/efetivo-executado/filtros', [RelatorioController::class, 'rotawebEfetivoExecutadoForm'])
            ->name('admin.relatorios.rotaweb.efetivo_executado.filtros');

        Route::get('/admin/relatorios/rotaweb/efetivo-executado/resultado', [RelatorioController::class, 'rotawebEfetivoExecutadoResultado'])
            ->name('admin.relatorios.rotaweb.efetivo_executado.resultado');

        Route::get('/admin/relatorios/rotaweb/console', function () {
            return view('admin.relatorios.rotaweb_console');
        })->name('admin.relatorios.rotaweb.console');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas P4 (can:isP4)
    |--------------------------------------------------------------------------
    |
    | Aqui entram os controllers específicos do P4
    | que já aplicam filtro por OPM + Policies.
    */
    Route::middleware('can:isP4')->prefix('p4')->name('p4.')->group(function () {

        // Viaturas do P4 (apenas da OPM do usuário)
        Route::resource('viaturas', P4ViaturaController::class)
            ->only(['index', 'show', 'edit', 'update']);

        // Manutenções do P4 (apenas as vinculadas a veículos da OPM do usuário)
        Route::resource('manutencoes', P4ManutencaoController::class)
            ->only(['index', 'show', 'edit', 'update']);

        // Rádios do P4 (apenas da OPM do usuário)
        Route::resource('radios', P4RadioController::class)
            ->only(['index', 'show', 'edit', 'update']);

        // Relatórios do P4 (escopo OPM)
        Route::get('/relatorios', [RelatorioController::class, 'porOpm'])->name('relatorios.index');
        Route::get('/relatorios/viaturas', [RelatorioController::class, 'viaturasP4'])->name('relatorios.viaturas');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas Compartilhadas (Admin + P4)
    |--------------------------------------------------------------------------
    */
    Route::get('/relatorios/viatura/{id}', [RelatorioController::class, 'detalhado'])
        ->name('relatorios.detalhado');

    /*
    |--------------------------------------------------------------------------
    | Rotas SISGP (auth + can:consultarSisgp)
    |--------------------------------------------------------------------------
    | Disponíveis para quem tem a permissão consultarSisgp (ex.: Admin e P4).
    */
    Route::prefix('sisgp')->name('sisgp.')->middleware('can:consultarSisgp')->group(function () {
        // Formulário/resultado de consulta de unidade
        Route::get('/unidades/consulta', [SisgpController::class, 'unidadeConsulta'])
            ->name('unidades.consulta');

        // Debug genérico (GET/POST)
        Route::get('/_debug', [SisgpController::class, 'debug'])
            ->name('debug');

        // Matriz de tentativas (paths x métodos)
        Route::get('/_debug-matrix', [SisgpController::class, 'debugMatrix'])
            ->name('debug.matrix');

        // Fuzzer de payload para POST /unidade
        Route::get('/_debug-fuzz-unidade', [SisgpController::class, 'debugFuzzUnidade'])
            ->name('debug.fuzz_unidade');
    });
});
