<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Opm;
use App\Models\Municipio;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    /**
     * Retorna OPMs filtradas pela "área" do select.
     *
     * IMPORTANTE:
     * No seu formulário, "Área" representa o CPR (grande comando).
     * Portanto aqui filtramos por opms.cpr (e não opms.area).
     *
     * Mantém o nome do método por compatibilidade com rotas/JS antigos.
     */
    public function opmsPorArea(Request $request)
    {
        $area = trim((string)$request->query('area', ''));
        if ($area === '') {
            return response()->json([]);
        }

        $opms = Opm::query()
            ->where('cpr', $area)
            ->orderBy('sigla')
            ->get(['id', 'sigla', 'nome']);

        $out = $opms->map(function ($o) {
            $label = $o->sigla ?: ($o->nome ?: ('OPM #' . $o->id));
            if ($o->sigla && $o->nome) $label = $o->sigla . ' — ' . $o->nome;
            return ['id' => $o->id, 'label' => $label];
        });

        return response()->json($out);
    }

    /**
     * Retorna municípios COBERTOS pela OPM (pivot opm_municipios).
     * Depende de existir tabela opm_municipios (opm_id, municipio_id).
     *
     * Retorna [{id,label}] onde:
     * - id = municipios.id
     * - label = municipios.nome
     */
    public function municipiosPorOpm(Request $request)
    {
        $opmId = (int)$request->query('opm_id', 0);
        if ($opmId <= 0) {
            return response()->json([]);
        }

        $municipios = Municipio::query()
            ->join('opm_municipios', 'opm_municipios.municipio_id', '=', 'municipios.id')
            ->where('opm_municipios.opm_id', $opmId)
            ->where('municipios.uf', 'RN')
            ->distinct()
            ->orderBy('municipios.nome')
            ->get(['municipios.id', 'municipios.nome']);

        $out = $municipios->map(fn($m) => ['id' => $m->id, 'label' => $m->nome]);

        return response()->json($out);
    }

    /**
     * Lista CPRs existentes na tabela opms (distinct).
     */
    public function cprs()
    {
        $cprs = Opm::query()
            ->select('cpr')
            ->whereNotNull('cpr')
            ->where('cpr', '<>', '')
            ->distinct()
            ->orderBy('cpr')
            ->pluck('cpr')
            ->values();

        return response()->json($cprs);
    }

    /**
     * ⚠️ OBSOLETO para o seu objetivo:
     * "cidadesPorCpr" usando opms.cidade (cidade sede) NÃO representa cobertura.
     *
     * Mantido para compatibilidade, mas não use no novo fluxo.
     */
    public function cidadesPorCpr(Request $request)
    {
        $cpr = trim((string) $request->query('cpr', ''));
        if ($cpr === '') return response()->json([]);

        $cidades = Opm::query()
            ->where('cpr', $cpr)
            ->select('cidade')
            ->whereNotNull('cidade')
            ->where('cidade', '<>', '')
            ->distinct()
            ->orderBy('cidade')
            ->pluck('cidade')
            ->values();

        return response()->json($cidades);
    }

    /**
     * Lista municípios COBERTOS por qualquer OPM do CPR (via pivot opm_municipios).
     * Retorna [{id,nome,uf}] para alimentar o select municipio_id.
     */
    public function municipiosPorCpr(Request $request)
    {
        $cpr = trim((string) $request->query('cpr', ''));
        if ($cpr === '') return response()->json([]);

        $municipios = Municipio::query()
            ->join('opm_municipios', 'opm_municipios.municipio_id', '=', 'municipios.id')
            ->join('opms', 'opms.id', '=', 'opm_municipios.opm_id')
            ->where('opms.cpr', $cpr)
            ->where('municipios.uf', 'RN')
            ->distinct()
            ->orderBy('municipios.nome')
            ->get(['municipios.id', 'municipios.nome', 'municipios.uf']);

        $out = $municipios->map(fn($m) => [
            'id' => $m->id,
            'nome' => $m->nome,
            'uf' => $m->uf,
        ]);

        return response()->json($out);
    }

    /**
     * Lista OPMs por CPR e (opcionalmente) cidade SEDE.
     *
     * ⚠️ Para o fluxo correto (cobertura), use apenas CPR -> OPM,
     * sem filtrar por cidade sede.
     */
    public function opmsPorCpr(Request $request)
    {
        $cpr = trim((string) $request->query('cpr', ''));
        $cidade = trim((string) $request->query('cidade', ''));

        if ($cpr === '') return response()->json([]);

        $q = Opm::query()->where('cpr', $cpr);

        if ($cidade !== '') {
            $q->where('cidade', $cidade);
        }

        $opms = $q->orderBy('sigla')->get(['id', 'sigla', 'nome']);

        $out = $opms->map(function ($o) {
            $label = $o->sigla ?: ($o->nome ?: ('OPM #' . $o->id));
            if ($o->sigla && $o->nome) $label = $o->sigla . ' — ' . $o->nome;
            return ['id' => $o->id, 'label' => $label];
        });

        return response()->json($out);
    }
}
