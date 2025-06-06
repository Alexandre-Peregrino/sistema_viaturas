<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Opm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RelatorioController extends Controller
{
    // ADMIN: Tela inicial de filtro
    public function index()
    {
        $opms = Opm::all();
        return view('admin.relatorios.index', compact('opms'));
    }

    // ADMIN: Resultado do filtro
    public function filtrar(Request $request)
    {
        $query = Veiculo::with('opm');

        if ($request->filled('opm_id')) {
            $query->where('opm_id', $request->opm_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $viaturas = $query->get();
        $opms = Opm::all();

        return view('admin.relatorios.index', compact('viaturas', 'opms'));
    }

    // ADMIN: Lista geral
    public function geral()
    {
        $viaturas = Veiculo::with('opm')->get();
        return view('admin.relatorios.index', compact('viaturas'));
    }

    // ADMIN/P4: Relatório detalhado de uma viatura
    public function detalhado($id)
    {
        $veiculo = Veiculo::with(['opm', 'manutencoes', 'abastecimentos'])->findOrFail($id);

        if (Gate::denies('view-veiculo', $veiculo)) {
            abort(403);
        }

        return view('relatorios.show', compact('veiculo'));
    }

    // ADMIN: Todas as viaturas com detalhes
    public function viaturas()
    {
        $viaturas = Veiculo::with(['opm', 'manutencoes', 'abastecimentos'])->get();
        return view('admin.relatorios.index', compact('viaturas'));
    }

    // P4: Viaturas da própria OPM
    public function porOpm()
    {
        $opmId = auth()->user()->opm_id;
        $viaturas = Veiculo::where('opm_id', $opmId)->with('opm')->get();

        return view('p4.relatorios.index', compact('viaturas'));
    }

    // P4: Viaturas da OPM com histórico
    public function viaturasP4()
    {
        $opmId = auth()->user()->opm_id;

        $viaturas = Veiculo::where('opm_id', $opmId)
            ->with(['opm', 'manutencoes', 'abastecimentos'])
            ->get();

        return view('p4.relatorios.index', compact('viaturas'));
    }
}
