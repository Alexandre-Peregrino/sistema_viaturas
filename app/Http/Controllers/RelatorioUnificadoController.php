<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Veiculo;
use App\Models\VeiculoLotacao;
use App\Models\Municipio;
use App\Models\Regiao;
use App\Models\Opm;

class RelatorioUnificadoController extends Controller
{
    /**
     * Tela de filtros unificados (checkbox/radio) com selects dinâmicos.
     */
    public function filtros()
    {
        $municipios = Municipio::orderBy('nome')->get(['id','nome']);

        // Carrega regiões por tipo (só as que você usar; pode ampliar depois)
        $regioes = [
            'CPC' => Regiao::where('tipo', 'CPC')->orderBy('nome')->get(['id','nome']),
            'CPM' => Regiao::where('tipo', 'CPM')->orderBy('nome')->get(['id','nome']),
            'CPR' => Regiao::where('tipo', 'CPR')->orderBy('nome')->get(['id','nome']),
            'BPM' => Regiao::where('tipo', 'BPM')->orderBy('nome')->get(['id','nome']),
            'CIPM'=> Regiao::where('tipo', 'CIPM')->orderBy('nome')->get(['id','nome']),
        ];

        $opms = Opm::orderBy('sigla')->get(['id','sigla']);

        // (Opcional) Diretorias/Órgãos – só se existir a tabela e se fizer sentido no seu domínio
        $diretorias = collect();
        if (Schema::hasTable('orgaos')) {
            $diretorias = \DB::table('orgaos')->orderBy('nome')->get(['id','nome']);
        }

        return view('admin.relatorios.filtros_unificados', compact('municipios','regioes','opms','diretorias'));
    }

    /**
     * Resultado unificado conforme o tipo selecionado.
     * Retorna veículos atualmente lotados (data_saida IS NULL) no alvo escolhido.
     */
    public function resultado(Request $request)
    {
        $tipo = $request->string('tipo')->value(); // municipio|cpr|cpc|cpm|opm|diretoria
        $ids  = collect($request->input('ids', []))->filter()->map(fn($v)=> (int)$v)->values();

        if (!$tipo || $ids->isEmpty()) {
            return back()->with('error', 'Selecione o tipo e pelo menos um item.');
        }

        // Base: lotações abertas
        $lot = VeiculoLotacao::query()->whereNull('data_saida');

        switch ($tipo) {
            case 'municipio':
                $lot->whereIn('municipio_id', $ids);
                break;

            case 'opm':
                $lot->whereIn('opm_id', $ids);
                break;

            case 'cpr':   // regiões do tipo CPR
            case 'cpc':   // comando de policiamento da capital
            case 'cpm':   // comando de policiamento metropolitano
            case 'bpm':   // batalhão
            case 'cipm':  // companhia independente
            {
                $tipoMap = strtoupper($tipo); // CPR/CPC/CPM/BPM/CIPM
                // OBS: por ora pega OPMs com regiao_id igual aos IDs selecionados.
                // Se quiser incluir descendentes (região pai -> filhos), depois ampliamos com recursão.
                $regiaoIds = $ids;

                // OPMs diretamente vinculadas às regiões selecionadas
                $opmIds = Opm::whereIn('regiao_id', $regiaoIds)->pluck('id');
                if ($opmIds->isEmpty()) {
                    $veiculos = collect();
                    return view('admin.relatorios.resultados.unificado', compact('veiculos','tipo','ids'));
                }
                $lot->whereIn('opm_id', $opmIds);
                break;
            }

            case 'diretoria':
                // Só como placeholder: normalmente "diretoria" não agrupa veículos diretamente.
                // Você pode decidir a regra (ex.: diretorias -> regioes/OPMs) e substituir aqui.
                // Por ora, não retorna nada para evitar resultado enganoso:
                $veiculos = collect();
                return view('admin.relatorios.resultados.unificado', compact('veiculos','tipo','ids'));

            default:
                return back()->with('error', 'Tipo inválido.');
        }

        // Carrega veículos atuais com relacionamentos úteis
        $veiculos = Veiculo::whereHas('lotacoes', fn($q) => $q->whereKey($lot->clone()->select('id')))
            ->with([
                'opm',
                'lotacoes' => fn($q)=> $q->whereNull('data_saida')->with('municipio'),
            ])
            ->orderBy('prefixo')
            ->get();

        return view('admin.relatorios.resultados.unificado', compact('veiculos','tipo','ids'));
    }
}
