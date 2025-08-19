<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SisgpService;

class SisgpController extends Controller
{
    public function __construct()
    {
        // Libera para Admin e P4 (gate definido no AuthServiceProvider)
        $this->middleware('can:consultarSisgp');
        // Se quiser apenas Admin: $this->middleware('can:isAdmin');
    }

    /**
     * Tela simples para consultar unidades (exibe formulário e resultado).
     * Ajuste a view 'sisgp.unidades.consulta' conforme sua necessidade.
     */
    public function unidadeConsulta(Request $request, SisgpService $sisgp)
    {
        $filtros = $request->only(['sigla', 'municipio', 'codigo', 'opm_id', 'uf']);

        // Se a API expuser uma rota GET compatível, use:
        $dados = $sisgp->consultarUnidadeGet($filtros);
        // Se for POST, troque para:
        // $dados = $sisgp->consultarUnidadePost($filtros);

        return view('sisgp.unidades.consulta', [
            'filtros' => $filtros,
            'dados'   => $dados,
        ]);
    }

    /**
     * Debug genérico para qualquer path do SISGP.
     * Ex.: /sisgp/_debug?p=policiais
     *      /sisgp/_debug?p=unidade&post=1&ce_unidade=797
     */
    public function debug(Request $request, SisgpService $sisgp)
    {
        // Default para um endpoint que sabemos responder 200 (sanity check):
        $path = $request->query('p', 'policiais');
        $usePost = $request->boolean('post');
        $params = $request->except(['p', 'post']);

        $resp = $sisgp->probe($usePost ? 'POST' : 'GET', $path, $params, $usePost);

        return response()->json([
            'debug'    => $sisgp->debugInfo(),
            'response' => $resp,
        ]);
    }

    /**
     * Varre uma matriz de paths/métodos para descobrir quais existem/funcionam.
     * Ex.: /sisgp/_debug-matrix?sigla=1%C2%BABPM
     */
    public function debugMatrix(Request $request, SisgpService $sisgp)
    {
        $params = $request->except(['post', 'p']);

        $paths = [
            'unidade/consulta',
            'unidade/consultar',
            'unidades/consulta',
            'unidades/consultar',
            'unidade',
            'unidades',
            'opm/consulta',
            'opm/consultar',
            'opm',
            'policiais', // sanity check conhecido (GET)
        ];

        $results = [];
        foreach ($paths as $p) {
            foreach (['GET', 'POST'] as $m) {
                $ok = $sisgp->probe($m, $p, $params, $m === 'POST');
                $results[] = [
                    'method'  => $m,
                    'path'    => $p,
                    'status'  => $ok['status']        ?? null,
                    'ok'      => $ok['ok']            ?? false,
                    'url'     => $ok['url']           ?? null,
                    'err'     => $ok['error']         ?? null,
                    'ctype'   => $ok['content_type']  ?? null,
                    'len'     => $ok['body_len']      ?? null,
                    'sample'  => $ok['body_sample']   ?? null,
                ];
            }
        }

        return response()->json([
            'debug'  => $sisgp->debugInfo(),
            'matrix' => $results,
        ]);
    }

    /**
     * Fuzzer para descobrir o payload exato aceito pelo POST /unidade.
     * Acesse: /sisgp/_debug-fuzz-unidade?ce_unidade=797
     *         /sisgp/_debug-fuzz-unidade?sigla=1%C2%BABPM
     *         /sisgp/_debug-fuzz-unidade?municipio=Natal
     */
    public function debugFuzzUnidade(Request $request, SisgpService $sisgp)
    {
        // Valores fornecidos na query serão usados nos payloads candidatos
        $vals = $request->only(['sigla', 'municipio', 'codigo', 'ce_unidade', 'uf']);

        // Candidatos JSON
        $jsonPayloads = array_filter([
            // diretos
            ['sigla' => $vals['sigla'] ?? null, 'municipio' => $vals['municipio'] ?? null, 'codigo' => $vals['codigo'] ?? null, 'ce_unidade' => $vals['ce_unidade'] ?? null, 'uf' => $vals['uf'] ?? null],
            ['codigo' => $vals['codigo'] ?? null],
            ['ce_unidade' => $vals['ce_unidade'] ?? null],

            // aninhados
            ['filtro'  => array_filter([
                'sigla'       => $vals['sigla'] ?? null,
                'municipio'   => $vals['municipio'] ?? null,
                'codigo'      => $vals['codigo'] ?? null,
                'ce_unidade'  => $vals['ce_unidade'] ?? null,
                'uf'          => $vals['uf'] ?? null,
            ], fn($v) => $v !== null && $v !== '')],
            ['unidade' => array_filter([
                'sigla'       => $vals['sigla'] ?? null,
                'municipio'   => $vals['municipio'] ?? null,
                'codigo'      => $vals['codigo'] ?? null,
                'ce_unidade'  => $vals['ce_unidade'] ?? null,
            ], fn($v) => $v !== null && $v !== '')],

            // variações de nome de chave comum em integrações legadas
            ['cd_unidade'     => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['idUnidade'      => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['unidadeId'      => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['codigoUnidade'  => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],

            // com sistema/cpf redundantes no body
            ['sistema'        => 'ROTAWEB', 'ce_unidade' => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['cpf'            => env('SISGP_USER'), 'ce_unidade' => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['sistema'        => 'ROTAWEB', 'unidade' => ['codigo' => $vals['ce_unidade'] ?? $vals['codigo'] ?? null]],
        ], function ($p) {
            // mantém apenas payloads com algum valor realmente definido
            return count(array_filter($p, function ($v) {
                if (is_array($v)) {
                    return count(array_filter($v, fn($x) => $x !== null && $x !== '')) > 0;
                }
                return $v !== null && $v !== '';
            })) > 0;
        });

        // Candidatos FORM (x-www-form-urlencoded)
        $formPayloads = array_filter([
            array_filter([
                'sigla'      => $vals['sigla'] ?? null,
                'municipio'  => $vals['municipio'] ?? null,
                'codigo'     => $vals['codigo'] ?? null,
                'ce_unidade' => $vals['ce_unidade'] ?? null,
                'uf'         => $vals['uf'] ?? null,
            ], fn($v) => $v !== null && $v !== ''),

            // aninhados estilo PHP
            array_filter([
                'filtro[sigla]'      => $vals['sigla'] ?? null,
                'filtro[municipio]'  => $vals['municipio'] ?? null,
                'filtro[codigo]'     => $vals['codigo'] ?? null,
                'filtro[ce_unidade]' => $vals['ce_unidade'] ?? null,
            ], fn($v) => $v !== null && $v !== ''),

            array_filter([
                'unidade[sigla]'      => $vals['sigla'] ?? null,
                'unidade[codigo]'     => $vals['codigo'] ?? null,
                'unidade[ce_unidade]' => $vals['ce_unidade'] ?? null,
            ], fn($v) => $v !== null && $v !== ''),

            // diretos
            ['codigo'     => $vals['codigo'] ?? null],
            ['ce_unidade' => $vals['ce_unidade'] ?? null],

            // variações de chave
            ['cd_unidade'    => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['idUnidade'     => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['unidadeId'     => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['codigoUnidade' => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],

            // com sistema/cpf
            ['sistema' => 'ROTAWEB', 'ce_unidade' => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['cpf'     => env('SISGP_USER'), 'ce_unidade' => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
            ['sistema' => 'ROTAWEB', 'unidade[codigo]' => $vals['ce_unidade'] ?? $vals['codigo'] ?? null],
        ], function ($p) {
            return count(array_filter($p, fn($v) => $v !== null && $v !== '')) > 0;
        });

        $results = [];

        // Testes JSON
        foreach ($jsonPayloads as $idx => $payload) {
            $r = $sisgp->probeJson('unidade', $payload);
            $results[] = [
                'kind'   => 'JSON',
                'idx'    => $idx,
                'body'   => $payload,
                'status' => $r['status']       ?? null,
                'ok'     => $r['ok']           ?? false,
                'url'    => $r['url']          ?? null,
                'ctype'  => $r['content_type'] ?? null,
                'len'    => $r['body_len']     ?? null,
                'sample' => $r['body_sample']  ?? null,
                'err'    => $r['error']        ?? null,
            ];
        }

        // Testes FORM
        foreach ($formPayloads as $idx => $payload) {
            $r = $sisgp->probeForm('unidade', $payload);
            $results[] = [
                'kind'   => 'FORM',
                'idx'    => $idx,
                'body'   => $payload,
                'status' => $r['status']       ?? null,
                'ok'     => $r['ok']           ?? false,
                'url'    => $r['url']          ?? null,
                'ctype'  => $r['content_type'] ?? null,
                'len'    => $r['body_len']     ?? null,
                'sample' => $r['body_sample']  ?? null,
                'err'    => $r['error']        ?? null,
            ];
        }

        return response()->json([
            'debug'  => $sisgp->debugInfo(),
            'trials' => $results,
        ]);
    }
}
