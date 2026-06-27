<?php

namespace App\Http\Controllers;

use App\Models\PrixM3;
use Illuminate\Http\Request;

class PrixM3Controller extends Controller
{
    public function index()
    {
        $prixList = PrixM3::orderByDesc('year')->get();
        return view('prix_m3.index', compact('prixList'));
    }

    public function create()
    {
        return view('prix_m3.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year'           => 'required|integer|min:2000|max:2100|unique:prix_m3,year',
            'price'          => 'required|numeric|min:0',
            'activation_fee' => 'nullable|numeric|min:0',
        ]);

        PrixM3::create($data);

        return redirect()->route('prix-m3.index')
            ->with('success', 'تم إضافة السعر بنجاح.');
    }

    public function edit(PrixM3 $prixM3)
    {
        return view('prix_m3.edit', compact('prixM3'));
    }

    public function update(Request $request, PrixM3 $prixM3)
    {
        $data = $request->validate([
            'year'           => 'required|integer|min:2000|max:2100|unique:prix_m3,year,' . $prixM3->id,
            'price'          => 'required|numeric|min:0',
            'activation_fee' => 'nullable|numeric|min:0',
        ]);

        $prixM3->update($data);

        return redirect()->route('prix-m3.index')
            ->with('success', 'تم تحديث السعر بنجاح.');
    }

    public function destroy(PrixM3 $prixM3)
    {
        $prixM3->delete();
        return redirect()->route('prix-m3.index')
            ->with('success', 'تم حذف السعر.');
    }
}
