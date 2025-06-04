<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VeiculoController;

// Rotas públicas
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {

    // Página inicial após login
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    // Rotas do perfil ADMIN
    Route::middleware('can:isAdmin')->group(function () {
        Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');

        // CRUD completo de viaturas (exceto show)
        Route::resource('/admin/viaturas', VeiculoController::class)
            ->except(['show'])
            ->names('admin.viaturas');
    });

    // Rotas do perfil P4
    Route::middleware('can:isP4')->group(function () {
        Route::get('/p4/viaturas', [VeiculoController::class, 'minhasViaturas'])->name('p4.viaturas.index');
        Route::get('/p4/viaturas/{id}/editar', [VeiculoController::class, 'editarRestrito'])->name('p4.viaturas.editar');
        Route::put('/p4/viaturas/{id}', [VeiculoController::class, 'atualizarRestrito'])->name('p4.viaturas.update');
    });

});
