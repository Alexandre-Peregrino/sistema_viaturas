<?php

namespace App\Http\Controllers;

use App\Models\Radio;
use App\Models\Opm;
use Illuminate\Http\Request;

class RadioController extends Controller
{
    /**
     * Lista todos os rádios.
     */
    public function index()
    {
        // Carrega os rádios com suas OPMs
        $radios = Radio::with('opm')->get();
        return view('admin.radios.index', compact('radios'));
    }

    /**
     * Exibe o formulário para criar um novo rádio.
     */
    public function create()
    {
        $opms = Opm::all();
        return view('admin.radios.create', compact('opms'));
    }

    /**
     * Armazena um novo rádio no banco de dados.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_serie' => 'required|string|unique:radios,numero_serie|max:255',
            'marca'        => 'required|string|max:255',
            'modelo'       => 'required|string|max:255',
            'status'       => 'required|string|max:255',
            'observacao'   => 'nullable|string',
            'opm_id'       => 'nullable|exists:opms,id',
        ]);

        Radio::create($validated);

        return redirect()->route('admin.radios.index')->with('success', 'Rádio cadastrado com sucesso!');
    }

    /**
     * Exibe o formulário para editar um rádio existente.
     */
    public function edit($id)
    {
        $radio = Radio::findOrFail($id);
        $opms = \App\Models\Opm::all();
        return view('admin.radios.edit', compact('radio', 'opms'));
    }

    /**
     * Atualiza um rádio existente no banco de dados.
     */
    public function update(Request $request, $id)
    {
        $radio = Radio::findOrFail($id);

        $validated = $request->validate([
            'numero_serie' => 'required|string|unique:radios,numero_serie,' . $radio->id . '|max:255',
            'marca'        => 'required|string|max:255',
            'modelo'       => 'required|string|max:255',
            'status'       => 'required|string|max:255',
            'observacao'   => 'nullable|string',
            'opm_id'       => 'nullable|exists:opms,id',
        ]);

        $radio->update($validated);

        return redirect()->route('admin.radios.index')->with('success', 'Rádio atualizado com sucesso!');
    }

    /**
     * Exclui um rádio do banco de dados.
     */
    public function destroy($id)
    {
        $radio = Radio::findOrFail($id);
        $radio->delete();

        return redirect()->route('admin.radios.index')->with('success', 'Rádio excluído com sucesso!');
    }
}
