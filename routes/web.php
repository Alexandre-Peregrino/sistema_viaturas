<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\ManutencaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\MarcaModeloController;

// ROTAS PÚBLICAS
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ROTAS PROTEGIDAS
Route::middleware('auth')->group(function () {

    Route::get('/home', fn () => view('home'))->name('home');

    // ROTAS ADMIN
    Route::middleware('can:isAdmin')->group(function () {
        Route::resource('/admin/usuarios', UsuarioController::class)->except(['show'])->names('admin.usuarios');

        Route::resource('/admin/viaturas', VeiculoController::class)->except(['show'])->names('admin.viaturas');
        Route::resource('/admin/manutencoes', ManutencaoController::class)->except(['show'])->names('admin.manutencoes');
        Route::resource('/admin/radios', RadioController::class)->except(['show'])->names('admin.radios');

        Route::get('/admin/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('/admin/relatorios/filtrar', [RelatorioController::class, 'filtrar'])->name('relatorios.filtrar');
        Route::get('/admin/relatorios/geral', [RelatorioController::class, 'geral'])->name('admin.relatorios.geral');
        Route::get('/admin/relatorios/viaturas', [RelatorioController::class, 'viaturas'])->name('admin.relatorios.viaturas');

        Route::resource('/admin/marcas-modelos', MarcaModeloController::class)->names('admin.marcas_modelos');
    });

    // ROTAS P4
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

        // Relatórios
        Route::get('/relatorios', [RelatorioController::class, 'porOpm'])->name('relatorios.index');
        Route::get('/relatorios/viaturas', [RelatorioController::class, 'viaturasP4'])->name('relatorios.viaturas');
    });

    // ROTA DE DETALHAMENTO (Admin + P4)
    Route::get('/relatorios/viatura/{id}', [RelatorioController::class, 'detalhado'])
        ->name('relatorios.detalhado');
});
