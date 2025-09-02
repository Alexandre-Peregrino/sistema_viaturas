<?php

namespace App\Http\Controllers\P4;

use App\Http\Controllers\Controller;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class ViaturaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']); // + seu middleware de papel/role se houver
    }

    /**
     * Lista APENAS as viaturas da OPM do usuário (P4).
     */
    public function index(Request $request)
    {
        $opmId = (int)auth()->user()->opm_id;

        $viaturas = Veiculo::query()
            ->where('opm_id', $opmId)
            ->orderByDesc('id')
            ->paginate(20);

        // Autoriza coleção (Admin e P4 liberados). Policy: ViaturaPolicy@viewAny
        $this->authorize('viewAny', Veiculo::class);

        return view('p4.viaturas.index', compact('viaturas'));
    }

    /**
     * Detalhe da viatura — bloqueia acesso se não for da mesma OPM do P4.
     * (Admin sempre pode; P4 só se opm_id bater). Policy: ViaturaPolicy@view
     */
    public function show(Veiculo $viatura)
    {
        $this->authorize('view', $viatura);
        return view('p4.viaturas.show', compact('viatura'));
    }

    /**
     * Form de edição — mesmo critério de autorização do show.
     * Policy: ViaturaPolicy@update
     */
    public function edit(Veiculo $viatura)
    {
        $this->authorize('update', $viatura);
        return view('p4.viaturas.edit', compact('viatura'));
    }

    /**
     * Atualiza campos permitidos ao P4.
     */
    public function update(Request $request, Veiculo $viatura)
    {
        $this->authorize('update', $viatura);

        $data = $request->validate([
            'prefixo'       => 'required|string|max:50',
            'marca_modelo'  => 'nullable|string|max:150',
            'tipo_veiculo'  => 'nullable|string|max:100',
        ]);

        $viatura->update($data);

        return redirect()
            ->route('p4.viaturas.index')
            ->with('success', 'Viatura atualizada.');
    }
}
