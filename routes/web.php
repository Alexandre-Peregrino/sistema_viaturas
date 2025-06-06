<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\ManutencaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\RadioController;

// ROTAS PÚBLICAS
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ROTAS PROTEGIDAS
Route::middleware('auth')->group(function () {

    Route::get('/home', fn() => view('home'))->name('home');

    // ADMIN
    Route::middleware('can:isAdmin')->group(function () {
        Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
        Route::resource('/admin/viaturas', VeiculoController::class)->except(['show'])->names('admin.viaturas');
        Route::resource('/admin/manutencoes', ManutencaoController::class)->except(['show'])->names('admin.manutencoes');
        Route::resource('/admin/radios', RadioController::class)->except(['show'])->names('admin.radios');
        Route::get('/admin/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('/admin/relatorios/filtrar', [RelatorioController::class, 'filtrar'])->name('relatorios.filtrar');
        Route::get('/admin/relatorios/geral', [RelatorioController::class, 'geral'])->name('admin.relatorios.geral');
        Route::get('/admin/relatorios/viaturas', [RelatorioController::class, 'viaturas'])->name('admin.relatorios.viaturas');
        Route::resource('/admin/marcas-modelos', \App\Http\Controllers\MarcaModeloController::class)->names('admin.marcas_modelos');

    });

    // P4
    Route::middleware('can:isP4')->group(function () {
        Route::get('/p4/viaturas', [VeiculoController::class, 'minhasViaturas'])->name('p4.viaturas.index');
        Route::get('/p4/viaturas/{id}/editar', [VeiculoController::class, 'editarRestrito'])->name('p4.viaturas.editar');
        Route::put('/p4/viaturas/{id}', [VeiculoController::class, 'atualizarRestrito'])->name('p4.viaturas.update');

        Route::get('/p4/manutencoes', [ManutencaoController::class, 'minhasManutencoes'])->name('p4.manutencoes.index');
        Route::get('/p4/manutencoes/{id}/editar', [ManutencaoController::class, 'editarRestrito'])->name('p4.manutencoes.editar');
        Route::put('/p4/manutencoes/{id}', [ManutencaoController::class, 'atualizarRestrito'])->name('p4.manutencoes.atualizar');

        Route::get('/p4/radios', [RadioController::class, 'meusRadios'])->name('p4.radios.index');

        Route::get('/p4/relatorios', [RelatorioController::class, 'porOpm'])->name('p4.relatorios.index');
        Route::get('/p4/relatorios/viaturas', [RelatorioController::class, 'viaturasP4'])->name('p4.relatorios.viaturas');
    });

    // ROTA DE DETALHAMENTO (acesso permitido a Admin e P4)
    Route::get('/relatorios/viatura/{id}', [RelatorioController::class, 'detalhado'])
        ->name('relatorios.detalhado');
});
