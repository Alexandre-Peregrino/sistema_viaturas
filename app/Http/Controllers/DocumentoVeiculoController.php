<?php

namespace App\Http\Controllers;

use App\Models\DocumentoVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class DocumentoVeiculoController extends Controller
{
    // ADMIN: Lista todos os documentos
    public function index()
    {
        $documentos = DocumentoVeiculo::with('veiculo')->get();
        return view('admin.documentos.index', compact('documentos'));
    }

    // ADMIN: Mostra o form de criação
    public function create()
    {
        $veiculos = Veiculo::all();
        return view('admin.documentos.create', compact('veiculos'));
    }

    // ADMIN: Armazena novo documento
    public function store(Request $request)
    {
        $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'tipo' => 'required|string',
            'numero' => 'nullable|string',
            'validade' => 'nullable|date',
            'observacao' => 'nullable|string',
        ]);

        DocumentoVeiculo::create($request->all());

        return redirect()->route('admin.documentos.index')->with('success', 'Documento criado com sucesso!');
    }

    // ADMIN: Mostra o form de edição
    public function edit($id)
    {
        $documento = DocumentoVeiculo::findOrFail($id);
        $veiculos = Veiculo::all();
        return view('admin.documentos.edit', compact('documento', 'veiculos'));
    }

    // ADMIN: Atualiza documento
    public function update(Request $request, $id)
    {
        $documento = DocumentoVeiculo::findOrFail($id);

        $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'tipo' => 'required|string',
            'numero' => 'nullable|string',
            'validade' => 'nullable|date',
            'observacao' => 'nullable|string',
        ]);

        $documento->update($request->all());

        return redirect()->route('admin.documentos.index')->with('success', 'Documento atualizado com sucesso!');
    }

    // ADMIN: Remove documento
    public function destroy($id)
    {
        $documento = DocumentoVeiculo::findOrFail($id);
        $documento->delete();

        return redirect()->route('admin.documentos.index')->with('success', 'Documento removido com sucesso!');
    }
}
