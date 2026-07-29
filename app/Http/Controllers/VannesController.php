<?php

namespace App\Http\Controllers;

use App\Models\Vannes;
use Illuminate\Http\Request;

class VannesController extends Controller
{
    public function index(Request $request)
    {
        $query = Vannes::withCount('clients');

        if ($request->filled('search')) {
            $query->where('reference', 'like', '%' . $request->input('search') . '%');
        }
        if ($request->filled('enable')) {
            $query->where('enable', $request->input('enable') === '1');
        }

        $vannes = $query->orderBy('reference')->paginate(25)->withQueryString();
        return view('vannes.index', compact('vannes'));
    }

    public function create()
    {
        return view('vannes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reference' => 'required|integer',
            'link'      => 'nullable|string|max:2',
            'enable'    => 'nullable|boolean',
            'consumed'  => 'nullable|boolean',
            'last_value'=> 'nullable|integer',
        ]);

        Vannes::create([
            'reference'  => $data['reference'],
            'link'       => $data['link'] ?? null,
            'enable'     => $request->boolean('enable'),
            'consumed'   => $request->boolean('consumed'),
            'last_value' => $data['last_value'] ?? 0,
        ]);

        return redirect()->route('vannes.index')
            ->with('success', 'تم إضافة العداد بنجاح.');
    }

    public function edit(Vannes $vanne)
    {
        return view('vannes.edit', compact('vanne'));
    }

    public function update(Request $request, Vannes $vanne)
    {
        $data = $request->validate([
            'reference'  => 'required|integer',
            'link'       => 'nullable|string|max:2',
            'enable'     => 'nullable|boolean',
            'consumed'   => 'nullable|boolean',
            'last_value' => 'nullable|integer',
        ]);

        $vanne->update([
            'reference'  => $data['reference'],
            'link'       => $data['link'] ?? null,
            'enable'     => $request->boolean('enable'),
            'consumed'   => $request->boolean('consumed'),
            'last_value' => $data['last_value'] ?? $vanne->last_value,
        ]);

        return redirect()->route('vannes.index')
            ->with('success', 'تم تحديث العداد بنجاح.');
    }

    public function destroy(Vannes $vanne)
    {
        $vanne->clients()->detach();
        $vanne->delete();
        return redirect()->route('vannes.index')
            ->with('success', 'تم حذف العداد.');
    }

    public function show(Vannes $vanne)
    {
        $vanne->load(['clients', 'soldes' => fn($q) => $q->with('client')->where('type', 'debit')->orderByDesc('date_transfert')]);

        $stats = [
            'count'   => $vanne->soldes->count(),
            'total'   => $vanne->soldes->sum('consume'),
            'average' => $vanne->soldes->avg('consume') ?? 0,
        ];

        return view('vannes.show', compact('vanne', 'stats'));
    }
}
