<?php

use Illuminate\Support\Facades\Route;

// Proxy RotaWeb
use App\Http\Controllers\RotawebProxyController;
use App\Http\Middleware\RequireApiKey;

/*
|--------------------------------------------------------------------------
| Ping da API
|--------------------------------------------------------------------------
*/
Route::middleware('api')->get('/ping', function () {
    return response()->json(['message' => 'API online']);
});

/*
|--------------------------------------------------------------------------
| RotaWeb Proxy API (protegida por X-API-Key)
| OBS: Em api.php o prefixo /api é automático. Aqui usamos apenas 'rotaweb'.
|--------------------------------------------------------------------------
*/
Route::prefix('rotaweb')
    ->middleware(RequireApiKey::class)
    ->group(function () {

        // Índice (evita 404 na raiz /api/rotaweb)
        Route::get('/', function () {
            return response()->json([
                'service'   => 'RotaWeb Proxy',
                'status'    => 'ok',
                'endpoints' => [
                    // saúde
                    'GET  /api/rotaweb/health',

                    // efetivo por operação (legado)
                    'GET  /api/rotaweb/efetivo/previsto?operacao={id}&inicio=YYYY-MM-DD&termino=YYYY-MM-DD',
                    'GET  /api/rotaweb/efetivo/executado?operacao={id}&inicio=YYYY-MM-DD&termino=YYYY-MM-DD',

                    // efetivo geral (sem operação)
                    'GET  /api/rotaweb/efetivo/previsto-geral?inicio=YYYY-MM-DD&termino=YYYY-MM-DD',
                    'GET  /api/rotaweb/efetivo/executado-geral?inicio=YYYY-MM-DD&termino=YYYY-MM-DD',
                    'GET  /api/rotaweb/efetivo/previsto-geral-com-vtr?inicio=YYYY-MM-DD&termino=YYYY-MM-DD',
                    'GET  /api/rotaweb/efetivo/executado-geral-com-vtr?inicio=YYYY-MM-DD&termino=YYYY-MM-DD',

                    // aliases antigos (sem op, com VTR)
                    'GET  /api/rotaweb/efetivo/previsto-sem-op?inicio=YYYY-MM-DD&termino=YYYY-MM-DD',
                    'GET  /api/rotaweb/efetivo/executado-sem-op?inicio=YYYY-MM-DD&termino=YYYY-MM-DD',

                    // utilidades
                    'POST /api/rotaweb/usuario',
                    'POST /api/rotaweb/unidades',

                    // auto-fiscalização / consultas
                    'POST /api/rotaweb/funcoes/autofiscalizaveis',
                    'POST /api/rotaweb/funcoes/autofiscalizar',      // início (single)
                    'POST /api/rotaweb/autofiscalizar/termino',      // término (single)
                    'POST /api/rotaweb/execucoes/autofiscalizaveis', // por CPF e período

                    // escalas
                    'POST /api/rotaweb/escalas/previstas',
                    'POST /api/rotaweb/escalas/executadas',
                    'POST /api/rotaweb/escalas/agente',              // data + lista de CPFs
                ],
            ]);
        });

        // saúde
        Route::get('/health', [RotawebProxyController::class, 'health']);

        // ----------------- Efetivo por OPERAÇÃO (legado) -----------------
        Route::get('/efetivo/previsto',  [RotawebProxyController::class, 'efetivoPrevisto']);
        Route::get('/efetivo/executado', [RotawebProxyController::class, 'efetivoExecutado']);

        // ----------------- Efetivo GERAL (sem operação) ------------------
        Route::get('/efetivo/previsto-geral',          [RotawebProxyController::class, 'efetivoPrevistoGeral']);
        Route::get('/efetivo/executado-geral',         [RotawebProxyController::class, 'efetivoExecutadoGeral']);
        Route::get('/efetivo/previsto-geral-com-vtr',  [RotawebProxyController::class, 'efetivoPrevistoGeralComVtr']);
        Route::get('/efetivo/executado-geral-com-vtr', [RotawebProxyController::class, 'efetivoExecutadoGeralComVtr']);

        // --------- Aliases antigos (sem operação, com info de VTR) -------
        Route::get('/efetivo/previsto-sem-op',   [RotawebProxyController::class, 'efetivoPrevistoSemOp']);
        Route::get('/efetivo/executado-sem-op',  [RotawebProxyController::class, 'efetivoExecutadoSemOp']);

        // ----------------------- Utilidades/descoberta --------------------
        Route::post('/usuario',  [RotawebProxyController::class, 'usuario']);
        Route::post('/unidades', [RotawebProxyController::class, 'unidades']);

        // ------------------- Auto-fiscalização / consultas ----------------
        Route::post('/funcoes/autofiscalizaveis', [RotawebProxyController::class, 'funcoesAutofiscalizaveis']);
        Route::post('/funcoes/autofiscalizar',    [RotawebProxyController::class, 'autofiscalizarInicio']); // início
        Route::post('/autofiscalizar/termino',    [RotawebProxyController::class, 'autofiscalizarTermino']); // término
        Route::post('/execucoes/autofiscalizaveis', [RotawebProxyController::class, 'execucoesAutofiscalizaveis']);

        // ---------------------------- Escalas -----------------------------
        Route::post('/escalas/previstas',   [RotawebProxyController::class, 'escalasPrevistas']);
        Route::post('/escalas/executadas',  [RotawebProxyController::class, 'escalasExecutadas']);
        Route::post('/escalas/agente',      [RotawebProxyController::class, 'escalasAgente']);
    });
