<?php

namespace App\Http\Controllers;

use App\Models\Radio;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class RadioController extends Controller
{
    // ADMIN: Lista todos os rádios
    public function index()
    {
        $radios = Radio::all();
        return view('admin.radios.index', compact('radios'));
    }

    // ADMIN: Formulário de criação
    public function create()
    {
        return view('admin.radios.create');
    }

    // ADMIN: Armazena novo rádio
    public function store(Request $request)
    {
        $request->validate([
            'numero_serie' => 'required|unique:radios,numero_serie',
            'modelo' => 'required|string',
            'status' => 'required|string',
        ]);

        Radio::create($request->all());

        return redirect()->route('admin.radios.index')->with('success', 'Rádio cadastrado com sucesso!');
    }

    // ADMIN: Formulário de edição
    public function edit($id)
    {
        $radio = Radio::findOrFail($id);
        return view('admin.radios.edit', compact('radio'));
    }

    // ADMIN: Atualiza o rádio
    public function update(Request $request, $id)
    {
        $radio = Radio::findOrFail($id);

        $request->validate([
            'numero_serie' => 'required|unique:radios,numero_serie,' . $radio->id,
            'modelo' => 'required|string',
            'status' => 'required|string',
        ]);

        $radio->update($request->all());

        return redirect()->route('admin.radios.index')->with('success', 'Rádio atualizado com sucesso!');
    }

    // ADMIN: Remove rádio
    public function destroy($id)
    {
        $radio = Radio::findOrFail($id);
        $radio->delete();

        return redirect()->route('admin.radios.index')->with('success', 'Rádio removido com sucesso!');
    }

    // P4: Lista apenas os rádios vinculados a viaturas da OPM do usuário
    public function meusRadios()
    {
        $opmId = auth()->user()->opm_id;

        $radios = Radio::whereIn('numero_serie', function ($query) use ($opmId) {
            $query->select('numero_serie_radio')
                ->from('veiculos')
                ->where('opm_id', $opmId);
        })->get();

        return view('p4.radios.index', compact('radios'));
    }
}
