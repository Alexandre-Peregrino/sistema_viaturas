<?php

namespace App\Http\Controllers;

use App\Models\Radio;
use Illuminate\Http\Request;

class RadioController extends Controller
{
    /**
     * Display a listing of the resource.
     * Lista todos os rádios.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $radios = Radio::all();
        return view('admin.radios.index', compact('radios'));
    }

    /**
     * Show the form for creating a new resource.
     * Exibe o formulário para criar um novo rádio.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.radios.create');
    }

    /**
     * Store a newly created resource in storage.
     * Armazena um novo rádio no banco de dados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero_serie' => 'required|string|unique:radios,numero_serie|max:255',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'status' => 'required|string|max:255', // NOVO: Campo status é obrigatório
            'observacao' => 'nullable|string',
        ]);

        Radio::create($request->all());

        return redirect()->route('admin.radios.index')->with('success', 'Rádio cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     * Exibe o formulário para editar um rádio existente.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $radio = Radio::findOrFail($id);
        return view('admin.radios.edit', compact('radio'));
    }

    /**
     * Update the specified resource in storage.
     * Atualiza um rádio existente no banco de dados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $radio = Radio::findOrFail($id);

        $request->validate([
            'numero_serie' => 'required|string|unique:radios,numero_serie,' . $radio->id . '|max:255',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'status' => 'required|string|max:255', // NOVO: Campo status é obrigatório
            'observacao' => 'nullable|string',
        ]);

        $radio->update($request->all());

        return redirect()->route('admin.radios.index')->with('success', 'Rádio atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     * Exclui um rádio do banco de dados.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $radio = Radio::findOrFail($id);
        $radio->delete();

        return redirect()->route('admin.radios.index')->with('success', 'Rádio excluído com sucesso!');
    }
}
