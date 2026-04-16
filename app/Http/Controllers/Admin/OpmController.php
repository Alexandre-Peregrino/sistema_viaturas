<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Opm;
use Illuminate\Http\Request;

class OpmController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $cpr = trim((string) $request->get('cpr', ''));
        $cidade = trim((string) $request->get('cidade', ''));

        $opms = Opm::query()
            ->when($q !== '', function ($query) use ($q) {
                // Agrupa o OR para não “vazar” e quebrar os demais filtros
                $query->where(function ($sub) use ($q) {
                    $sub->where('sigla', 'ILIKE', "%{$q}%")
                        ->orWhere('nome', 'ILIKE', "%{$q}%");
                });
            })
            ->when($cpr !== '', fn($query) => $query->where('cpr', $cpr))
            ->when($cidade !== '', fn($query) => $query->where('cidade', $cidade))
            ->withCount(['veiculos', 'usuarios'])
            ->orderBy('sigla')
            ->paginate(20)
            ->withQueryString();

        $cprs = Opm::query()
            ->select('cpr')
            ->whereNotNull('cpr')
            ->distinct()
            ->orderBy('cpr')
            ->pluck('cpr');

        $cidades = Opm::query()
            ->select('cidade')
            ->whereNotNull('cidade')
            ->distinct()
            ->orderBy('cidade')
            ->pluck('cidade');

        return view('admin.opms.index', compact('opms', 'cprs', 'cidades', 'q', 'cpr', 'cidade'));
    }

    public function create()
    {
        $opmsPai = Opm::orderBy('sigla')->get();
        return view('admin.opms.create', compact('opmsPai'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sigla' => 'required|string|max:255|unique:opms,sigla',
            'nome'  => 'nullable|string|max:255',

            // no seu banco essas colunas são NOT NULL
            'cpr'    => 'nullable|string|max:255',
            'area'   => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',

            'parent_opm_id' => 'nullable|exists:opms,id',
        ]);

        // Defaults compatíveis com seu padrão atual ("N/D")
        $data['cpr'] = $this->defaultND($data['cpr'] ?? null);
        $data['area'] = $this->defaultND($data['area'] ?? null);
        $data['cidade'] = $this->defaultND($data['cidade'] ?? null);

        Opm::create($data);

        return redirect()
            ->route('admin.opms.index')
            ->with('success', 'OPM criada com sucesso.');
    }

    public function edit(Opm $opm)
    {
        $opmsPai = Opm::where('id', '<>', $opm->id)->orderBy('sigla')->get();
        return view('admin.opms.edit', compact('opm', 'opmsPai'));
    }

    public function update(Request $request, Opm $opm)
    {
        $data = $request->validate([
            'sigla' => 'required|string|max:255|unique:opms,sigla,' . $opm->id,
            'nome'  => 'nullable|string|max:255',

            'cpr'    => 'nullable|string|max:255',
            'area'   => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',

            'parent_opm_id' => 'nullable|exists:opms,id',
        ]);

        $data['cpr'] = $this->defaultND($data['cpr'] ?? null);
        $data['area'] = $this->defaultND($data['area'] ?? null);
        $data['cidade'] = $this->defaultND($data['cidade'] ?? null);

        $opm->update($data);

        return redirect()
            ->route('admin.opms.index')
            ->with('success', 'OPM atualizada com sucesso.');
    }

    public function destroy(Opm $opm)
    {
        // Proteção básica (evita quebrar FK lógica do sistema)
        if ($opm->veiculos()->count() > 0 || $opm->usuarios()->count() > 0) {
            return redirect()
                ->route('admin.opms.index')
                ->with('error', 'Não é possível excluir: há veículos e/ou usuários vinculados.');
        }

        $opm->delete();

        return redirect()
            ->route('admin.opms.index')
            ->with('success', 'OPM excluída com sucesso.');
    }

    private function defaultND(?string $value): string
    {
        $v = trim((string) $value);
        return $v === '' ? 'N/D' : $v;
    }
}
