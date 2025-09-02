<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RotawebClient;
use Illuminate\Validation\Rule;

class RotawebProxyController extends Controller
{
    public function __construct(private RotawebClient $client) {}

    public function health()
    {
        return ['ok' => true];
    }

    public function efetivoPrevisto(Request $req)
    {
        $data = $req->validate([
            'operacao' => ['required'],
            'inicio'   => ['required', 'string'],
            'termino'  => ['required', 'string'],
        ]);

        try {
            return $this->client->efetivoPrevisto($data['operacao'], $data['inicio'], $data['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function efetivoExecutado(Request $req)
    {
        $data = $req->validate([
            'operacao' => ['required'],
            'inicio'   => ['required', 'string'],
            'termino'  => ['required', 'string'],
        ]);

        try {
            return $this->client->efetivoExecutado($data['operacao'], $data['inicio'], $data['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function efetivoPrevistoSemOp(Request $req)
    {
        $data = $req->validate([
            'inicio'  => ['required', 'string'],
            'termino' => ['required', 'string'],
        ]);

        try {
            return $this->client->efetivoPrevistoSemOp($data['inicio'], $data['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function efetivoExecutadoSemOp(Request $req)
    {
        $data = $req->validate([
            'inicio'  => ['required', 'string'],
            'termino' => ['required', 'string'],
        ]);

        try {
            return $this->client->efetivoExecutadoSemOp($data['inicio'], $data['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function funcoesAutofiscalizaveis(Request $req)
    {
        $data = $req->validate([
            'cpf'     => ['required', 'string'],
            'inicio'  => ['required', 'string'],
            'termino' => ['required', 'string'],
        ]);

        try {
            return $this->client->funcoesAutofiscalizaveis($data['cpf'], $data['inicio'], $data['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function autofiscalizarInicio(Request $req)
    {
        $data = $req->validate([
            'cpf'           => ['required', 'string'],
            'codigo_funcao' => ['required'],
            'inicio'        => ['required', 'string'],
            'termino'       => ['required', 'string'],
            'latitude'      => ['required', 'numeric'],
            'longitude'     => ['required', 'numeric'],
        ]);

        try {
            return $this->client->autofiscalizarInicio(
                $data['cpf'],
                $data['codigo_funcao'],
                $data['inicio'],
                $data['termino'],
                (float)$data['latitude'],
                (float)$data['longitude'],
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function escalasPrevistas(Request $req)
    {
        $data = $req->validate([
            'orgao'      => ['required', 'integer'],
            'n'          => ['required', 'integer', 'min:1'],
            'matriculas' => ['required', 'array', 'min:1'],
            'matriculas.*' => ['string'],
        ]);

        try {
            return $this->client->ultimasEscalasPrevistas($data['orgao'], $data['n'], $data['matriculas']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function escalasExecutadas(Request $req)
    {
        $data = $req->validate([
            'orgao'      => ['required', 'integer'],
            'n'          => ['required', 'integer', 'min:1'],
            'matriculas' => ['required', 'array', 'min:1'],
            'matriculas.*' => ['string'],
        ]);

        try {
            return $this->client->ultimasEscalasExecutadas($data['orgao'], $data['n'], $data['matriculas']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function usuario()
    {
        try {
            return $this->client->usuario(); // POST {}
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function unidades()
    {
        try {
            return $this->client->unidades(); // POST {}
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function efetivoPrevistoGeral(Request $r)
    {
        $v = $r->validate(['inicio' => 'required', 'termino' => 'required']);
        try {
            return $this->client->efetivoPrevistoGeral($v['inicio'], $v['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function efetivoExecutadoGeral(Request $r)
    {
        $v = $r->validate(['inicio' => 'required', 'termino' => 'required']);
        try {
            return $this->client->efetivoExecutadoGeral($v['inicio'], $v['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    /* (Opcional) versões COM VTR */
    public function efetivoPrevistoGeralComVtr(Request $r)
    {
        $v = $r->validate(['inicio' => 'required', 'termino' => 'required']);
        try {
            return $this->client->efetivoPrevistoGeralComVtr($v['inicio'], $v['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function efetivoExecutadoGeralComVtr(Request $r)
    {
        $v = $r->validate(['inicio' => 'required', 'termino' => 'required']);
        try {
            return $this->client->efetivoExecutadoGeralComVtr($v['inicio'], $v['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function execucoesAutofiscalizaveis(Request $r)
    {
        $v = $r->validate([
            'cpf'     => 'required',
            'inicio'  => 'required',
            'termino' => 'required',
        ]);
        try {
            return $this->client->execucoesAutofiscalizaveisCpfPeriodo($v['cpf'], $v['inicio'], $v['termino']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function escalasAgente(Request $r)
    {
        $v = $r->validate([
            'data'   => 'required',
            'cpfs'   => 'required|array|min:1',
            'cpfs.*' => 'string',
        ]);
        try {
            return $this->client->escalasAgente($v['data'], implode(', ', $v['cpfs']));
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function autofiscalizarTermino(Request $r)
    {
        $v = $r->validate([
            'cpf'             => 'required',
            'inicio'          => 'required',
            'termino'         => 'required',
            'codigo_execucao' => 'required',
        ]);
        try {
            return $this->client->autoFiscalizarTermino($v['cpf'], $v['inicio'], $v['termino'], $v['codigo_execucao']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }
}
