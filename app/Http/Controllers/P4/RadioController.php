<?php

namespace App\Http\Controllers\P4;

use App\Http\Controllers\Controller;
use App\Models\Radio;
use Illuminate\Http\Request;

class RadioController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']); // + middleware de papel/role se houver
    }

    public function index(Request $request)
    {
        $opmId = (int)auth()->user()->opm_id;

        $radios = Radio::query()
            ->where('opm_id', $opmId)
            // ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            // ->when($request->filled('modelo'), fn($q) => $q->where('modelo', $request->modelo))
            ->orderBy('id', 'desc')
            ->paginate(20);

        $this->authorize('viewAny', Radio::class);

        return view('p4.radios.index', compact('radios'));
    }

    public function show(Radio $radio)
    {
        $this->authorize('view', $radio);
        return view('p4.radios.show', compact('radio'));
    }

    public function edit(Radio $radio)
    {
        $this->authorize('update', $radio);
        return view('p4.radios.edit', compact('radio'));
    }

    public function update(Request $request, Radio $radio)
    {
        $this->authorize('update', $radio);

        $data = $request->validate([
            'status' => 'required|string|max:50',
            // demais campos permitidos ao P4
        ]);

        $radio->update($data);
        return redirect()->route('p4.radios.index')->with('success', 'Rádio atualizado.');
    }
}
