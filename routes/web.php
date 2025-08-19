<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\ManutencaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\MarcaModeloController;
use App\Http\Controllers\RelatorioUnificadoController;
use App\Http\Controllers\SisgpController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/home', fn () => view('home'))->name('home');
Route::get('/', fn () => redirect()->route('home'));
Route::get('/funcionalidades', fn () => view('funcionalidades'))->name('funcionalidades');

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
    */
    Route::middleware('can:isAdmin')->group(function () {
        // Usuários
        Route::resource('/admin/usuarios', UsuarioController::class)
            ->except(['show'])
            ->names('admin.usuarios');

        // Viaturas (CRUD + extras)
        Route::resource('/admin/viaturas', VeiculoController::class)
            ->except(['show'])
            ->names('admin.viaturas');

        Route::get('/admin/viaturas/relatorio', [VeiculoController::class, 'relatorio'])
            ->name('admin.viaturas.relatorio');

        Route::get('/admin/viaturas/por-opm', [VeiculoController::class, 'porOpm'])
            ->name('admin.viaturas.por_opm');

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
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas P4 (can:isP4)
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isP4')->prefix('p4')->name('p4.')->group(function () {
        // Viaturas
        Route::get('/viaturas', [VeiculoController::class, 'index'])->name('viaturas.index');
        Route::get('/viaturas/{id}/editar', [VeiculoController::class, 'editarRestrito'])->name('viaturas.editar');
        Route::put('/viaturas/{id}', [VeiculoController::class, 'atualizarRestrito'])->name('viaturas.update');

        // Manutenções
        Route::get('/manutencoes', [ManutencaoController::class, 'minhasManutencoes'])->name('manutencoes.index');
        Route::get('/manutencoes/{id}/editar', [ManutencaoController::class, 'editarRestrito'])->name('manutencoes.editar');
        Route::put('/manutencoes/{id}', [ManutencaoController::class, 'atualizarRestrito'])->name('manutencoes.atualizar');

        // Rádios
        Route::get('/radios', [RadioController::class, 'meusRadios'])->name('radios.index');

        // Relatórios (P4)
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
