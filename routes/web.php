<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OpmController;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterLdapController;

// Admin controllers (escopo global)
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\ManutencaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\MarcaModeloController;
use App\Http\Controllers\RelatorioUnificadoController;
use App\Http\Controllers\SisgpController;
use App\Http\Controllers\Admin\VeiculoSyncController;
use App\Http\Controllers\Admin\UsuariosLdapController;

// Perfil (completar cadastro)
use App\Http\Controllers\PerfilController;

// P4 controllers (escopo OPM do usuário)
use App\Http\Controllers\P4\ViaturaController as P4ViaturaController;
use App\Http\Controllers\P4\RadioController as P4RadioController;
use App\Http\Controllers\P4\ManutencaoController as P4ManutencaoController;

// Middlewares do "web" que geram cookie/sessão/CSRF (mantidos)
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\View\Middleware\ShareErrorsFromSession;


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
| HOME restrita (logado, mas ainda sem acesso)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/home-restrita', function () {
    return view('home_restrita');
})->name('home.restrita');

/*
|--------------------------------------------------------------------------
| Completar Cadastro (logado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/perfil/completar', [PerfilController::class, 'completar'])->name('perfil.completar');
    Route::post('/perfil/completar', [PerfilController::class, 'store'])->name('perfil.completar.store');
});


/*
| ------------------------------------------------------------------------
| Consultas (sem views) – (Admin + P4) OU Debug Token (ambiente local)
|--------------------------------------------------------------------------
| Fora do middleware 'auth', pois LDAP não funciona fora da rede do Estado.
| Aqui entra o middleware 'auth_or_debug'.
*/
Route::prefix('consultas')
    ->name('consultas.')
    ->middleware('auth_or_debug')
    ->group(function () {
        Route::get('/viaturas', [ConsultaController::class, 'viaturas'])->name('viaturas');
        Route::get('/opms', [ConsultaController::class, 'opms'])->name('opms');
        Route::get('/opms/search', [ConsultaController::class, 'opmsSearch'])
            ->name('opms.search');


        // Dashboard admin (se logado, você pode validar admin no controller)
        Route::get('/dashboard/admin', [ConsultaController::class, 'dashboardAdmin'])->name('dashboard.admin');
    });

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (auth + cadastro completo + aprovado)
|--------------------------------------------------------------------------
|
| 1) cadastro_completo: força completar cadastro
| 2) permitido: bloqueia acesso até admin liberar
|
*/
Route::middleware(['auth', 'cadastro_completo', 'permitido'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Rotas ADMIN (can:isAdmin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isAdmin')->group(function () {

        // LDAP (Admin) — endpoints para a tela /admin/usuarios
        Route::get('/admin/usuarios/ldap/search', [UsuariosLdapController::class, 'search'])
            ->name('admin.usuarios.ldap.search');

        Route::post('/admin/usuarios/ldap/import', [UsuariosLdapController::class, 'import'])
            ->name('admin.usuarios.ldap.import');

        // OPMs (Admin)
        Route::resource('/admin/opms', OpmController::class)
            ->except(['show'])
            ->names('admin.opms');

        // Usuários
        Route::resource('/admin/usuarios', UsuarioController::class)
            ->except(['show', 'create', 'store'])
            ->names('admin.usuarios');

        // Viaturas (CRUD + extras)
        Route::resource('/admin/viaturas', VeiculoController::class)
            ->except(['show'])
            ->names('admin.viaturas');

        Route::get('/admin/viaturas/relatorio', [VeiculoController::class, 'relatorio'])
            ->name('admin.viaturas.relatorio');

        Route::get('/admin/viaturas/por-opm', [VeiculoController::class, 'porOpm'])
            ->name('admin.viaturas.por_opm');

        /**
         * NOVO: consulta simples no BD (por placa) para a tela /admin/viaturas
         * (não depende do ROTA)
         */
        Route::post('/admin/viaturas/db/probe', [VeiculoController::class, 'probeDb'])
            ->name('admin.viaturas.db.probe');

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
        // ✅ AJAX (dependentes): Área -> OPM -> Municípios/Cidades
        Route::get('/admin/ajax/opms-por-area', [\App\Http\Controllers\Admin\AjaxController::class, 'opmsPorArea'])
            ->name('admin.ajax.opms_por_area');

        Route::get('/admin/ajax/municipios-por-opm', [\App\Http\Controllers\Admin\AjaxController::class, 'municipiosPorOpm'])
            ->name('admin.ajax.municipios_por_opm');

        // ✅ NOVO: CPR -> Cidades -> OPMs (para formulário de viaturas)
        Route::get('/admin/ajax/cprs', [\App\Http\Controllers\Admin\AjaxController::class, 'cprs'])
            ->name('admin.ajax.cprs');

        Route::get('/admin/ajax/cidades-por-cpr', [\App\Http\Controllers\Admin\AjaxController::class, 'cidadesPorCpr'])
            ->name('admin.ajax.cidades_por_cpr');

        Route::get('/admin/ajax/opms-por-cpr', [\App\Http\Controllers\Admin\AjaxController::class, 'opmsPorCpr'])
            ->name('admin.ajax.opms_por_cpr');

        Route::get('/admin/ajax/municipios-por-cpr', [\App\Http\Controllers\Admin\AjaxController::class, 'municipiosPorCpr'])
            ->name('admin.ajax.municipios_por_cpr');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas P4 (can:isP4)
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isP4')->prefix('p4')->name('p4.')->group(function () {

        Route::resource('viaturas', P4ViaturaController::class)
            ->only(['index', 'show', 'edit', 'update']);

        Route::resource('manutencoes', P4ManutencaoController::class)
            ->only(['index', 'show', 'edit', 'update']);

        Route::resource('radios', P4RadioController::class)
            ->only(['index', 'show', 'edit', 'update']);

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
    */
    Route::prefix('sisgp')->name('sisgp.')->middleware('can:consultarSisgp')->group(function () {
        Route::get('/unidades/consulta', [SisgpController::class, 'unidadeConsulta'])
            ->name('unidades.consulta');

        Route::get('/_debug', [SisgpController::class, 'debug'])
            ->name('debug');

        Route::get('/_debug-matrix', [SisgpController::class, 'debugMatrix'])
            ->name('debug.matrix');

        Route::get('/_debug-fuzz_unidade', [SisgpController::class, 'debugFuzzUnidade'])
            ->name('debug.fuzz_unidade');
    });
});
