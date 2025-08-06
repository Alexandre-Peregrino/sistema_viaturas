<?php

namespace App\Http\Controllers;

use App\Models\Opm; // Certifique-se de importar o modelo Opm
use App\Models\Veiculo;
use App\Models\Usuario;
use App\Models\Radio;
use App\Models\Manutencao;
use Illuminate\Http\Request;



class RelatorioController extends Controller
{
    // Método para o índice de relatórios (se houver uma página de índice geral)
    public function index()
    {
        // Este método pode ser ajustado para exibir um dashboard de relatórios
        // ou redirecionar para relatórios específicos como o geral ou de viaturas.
        return redirect()->route('admin.relatorios.geral'); // Exemplo de redirecionamento
    }

    // Método para o relatório geral (acesso admin)
    public function geral()
    {
        $opms = Opm::all(); // Busque todas as OPMs
        // Você pode adicionar a lógica para buscar dados de veículos, manutenções, etc. aqui
        // Por exemplo, para um relatório geral, você pode carregar todos os veículos ou estatísticas
        $veiculos = Veiculo::all(); // Exemplo: Carregar todos os veículos para o relatório

        // A view esperada é 'admin.relatorios.index' conforme a stack trace
        return view('admin.relatorios.index', compact('opms', 'veiculos'));
    }

    // Método para filtrar relatórios (ainda dentro do admin)
    public function filtrar(Request $request)
    {
        $query = Veiculo::query();

        if ($request->filled('opm_id')) {
            $query->where('opm_id', $request->opm_id);
        }

        // Adicione outros filtros conforme necessário (ex: por data, tipo de veículo, etc.)

        $veiculos = $query->get();
        $opms = Opm::all(); // Também passe as OPMs para o formulário de filtro na view

        // Retorna a mesma view 'admin.relatorios.index' com os resultados filtrados
        return view('admin.relatorios.index', compact('veiculos', 'opms'));
    }

    // Método para relatório de viaturas (admin)
    public function viaturas(Request $request)
    {
        $query = Veiculo::with('opm');

        if ($request->filled('opm_id')) {
            $query->where('opm_id', $request->opm_id);
        }

        if ($request->filled('tipos')) {
            $query->whereIn('tipo_veiculo', $request->tipos);
        }

        if ($request->filled('combustiveis')) {
            $query->whereIn('combustivel', $request->combustiveis);
        }

        if ($request->filled('tracoes')) {
            $query->whereIn('tracao', $request->tracoes);
        }

        $viaturas = $query->get();

        return view('admin.relatorios.resultados.viaturas', [
            'viaturas' => $viaturas,
            'titulo' => 'Relatório de Viaturas Filtrado'
        ]);
    }


    // Método para exibir os filtros do relatório de viaturas (Admin)
    public function viaturasFiltros()
    {
        $opms = Opm::all();
        $tipos = Veiculo::select('tipo_veiculo')->distinct()->pluck('tipo_veiculo');
        $combustiveis = Veiculo::select('combustivel')->distinct()->pluck('combustivel');
        $tracoes = Veiculo::select('tracao')->distinct()->pluck('tracao');

        return view('admin.relatorios.viaturas_filtros', compact('opms', 'tipos', 'combustiveis', 'tracoes'));
    }



    // Métodos para o perfil P4

    // Relatório por OPM (P4)
    public function porOpm()
    {
        $usuario = auth()->user();
        if ($usuario->opm_id) {
            $opm = Opm::find($usuario->opm_id);
            $veiculos = Veiculo::where('opm_id', $usuario->opm_id)->get();
            return view('p4.relatorios.index', compact('opm', 'veiculos')); // Supondo uma view 'p4.relatorios.index'
        }
        return view('p4.relatorios.index')->with('message', 'Nenhuma OPM associada.');
    }

    // Relatório de Viaturas para P4
    public function viaturasP4()
    {
        $usuario = auth()->user();
        if ($usuario->opm_id) {
            $veiculos = Veiculo::where('opm_id', $usuario->opm_id)->with('opm')->get();
            $opm = Opm::find($usuario->opm_id); // Pode ser útil para exibir o nome da OPM na view
            return view('p4.relatorios.viaturas', compact('veiculos', 'opm')); // Supondo uma view 'p4.relatorios.viaturas'
        }
        return view('p4.relatorios.viaturas')->with('message', 'Nenhuma OPM associada para filtrar viaturas.');
    }

    // ROTA DE DETALHAMENTO (Admin + P4)
    public function detalhado($id)
    {
        $veiculo = Veiculo::with(['opm', 'manutencoes', 'radio'])->findOrFail($id);
        // Implemente a lógica para exibir detalhes do veículo
        return view('detalhes_viatura', compact('veiculo')); // Supondo uma view para detalhes
    }

    // Exibir o formulário de filtros para o relatório de usuários (Admin)
    public function usuariosFiltros()
    {
        // Se houver algum dado necessário (como OPMs para filtro), carregue aqui:
        $opms = Opm::all(); // Opcional, só se quiser filtrar usuários por OPM

        return view('admin.relatorios.usuarios_filtros', compact('opms'));
    }

    public function usuariosResultado(Request $request)
    {
        $query = Usuario::query()->with('opm');

        if ($request->filled('perfis')) {
            $query->whereIn('perfil', $request->perfis);
        }

        if ($request->filled('opms')) {
            $query->whereIn('opm_id', $request->opms);
        }

        $usuarios = $query->get();
        $opms = Opm::all();

        return view('admin.relatorios.resultados.usuarios', compact('usuarios', 'opms'));
    }

    public function radiosFiltros()
    {
        $opms = Opm::all();
        $marcas = Radio::select('marca')->distinct()->pluck('marca');
        $modelos = Radio::select('modelo')->distinct()->pluck('modelo');
        $situacoes = Radio::select('status')->distinct()->pluck('status');

        return view('admin.relatorios.radios_filtros', compact('opms', 'marcas', 'modelos', 'situacoes'));
    }
    public function radiosResultado(Request $request)
    {
        $query = Radio::with('opm');

        if ($request->filled('opm_id')) {
            $query->where('opm_id', $request->opm_id);
        }

        if ($request->filled('marca')) {
            $query->where('marca', $request->marca);
        }

        if ($request->filled('modelo')) {
            $query->where('modelo', $request->modelo);
        }

        // Correção: aceitar múltiplos status (situações) do formulário
        if ($request->filled('situacoes')) {
            $query->whereIn('status', $request->situacoes);
        }

        $radios = $query->get();

        return view('admin.relatorios.radios_resultado', compact('radios'));
    }

    public function manutencoesFiltros()
    {
        $opms = Opm::all(); // Carregar todas as OPMs
        $tiposManutencao = Manutencao::select('tipo')->distinct()->pluck('tipo'); // Exemplo: tipos de manutenção

        return view('admin.relatorios.manutencoes_filtros', compact('opms', 'tiposManutencao'));
    }
    public function manutencoesResultado(Request $request)
    {
        $query = Manutencao::query();

        if ($request->filled('opm_id')) {
            $query->where('opm_id', $request->opm_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $manutencoes = $query->get();
        $opms = Opm::all(); // Para passar as OPMs novamente, se necessário

        return view('admin.relatorios.resultados.manutencoes', compact('manutencoes', 'opms'));
    }
}
