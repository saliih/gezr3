<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientActivation;
use App\Models\Vannes;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['vannes', 'activations']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cin', 'like', "%{$search}%")
                  ->orWhere('num_dossier', 'like', "%{$search}%")
                  ->orWhere('tel', 'like', "%{$search}%");
            });
        }

        if ($vanneIds = array_filter((array) $request->input('vannes', []))) {
            $query->whereHas('vannes', fn($q) => $q->whereIn('vannes.id', $vanneIds));
        }

        $clients = $query->orderBy('name')->paginate(25)->withQueryString();
        $year = (int) date('Y');
        $vannes = Vannes::orderBy('reference')->get();

        return view('clients.index', compact('clients', 'year', 'vannes'));
    }

    public function create()
    {
        $vannes = Vannes::orderBy('reference')->get();
        return view('clients.create', compact('vannes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'cin'         => 'nullable|string|max:8',
            'num_dossier' => 'nullable|string|max:50|unique:client,num_dossier',
            'tel'         => 'nullable|string|max:50',
            'comments'    => 'nullable|string',
            'gtree'       => 'nullable|integer',
            'mtree'       => 'nullable|integer',
            'otree'       => 'nullable|integer',
            'ptree'       => 'nullable|integer',
            'old_solde'   => 'nullable|numeric',
            'vannes'      => 'nullable|array',
            'vannes.*'    => 'exists:vannes,id',
        ]);

        $client = Client::create([
            'name'        => $data['name'],
            'cin'         => $data['cin'] ?? null,
            'num_dossier' => $data['num_dossier'] ?? null,
            'tel'         => $data['tel'] ?? null,
            'comments'    => $data['comments'] ?? null,
            'gtree'       => $data['gtree'] ?? 0,
            'mtree'       => $data['mtree'] ?? 0,
            'otree'       => $data['otree'] ?? 0,
            'ptree'       => $data['ptree'] ?? 0,
            'old_solde'   => $data['old_solde'] ?? 0,
        ]);

        if (!empty($data['vannes'])) {
            $client->vannes()->sync($data['vannes']);
        }

        return redirect()->route('clients.index')
            ->with('success', 'تم إضافة العميل بنجاح.');
    }

    public function show(Client $client)
    {
        $client->load(['vannes', 'activations', 'soldes.vannes']);

        // Group soldes by year then by vannes_id
        $soldesByYear = $client->soldes
            ->sortByDesc('date_transfert')
            ->groupBy(fn($s) => $s->date_transfert?->year ?? '—')
            ->sortKeysDesc();

        // Last reminder across all soldes
        $lastReminder = $client->soldes
            ->sortByDesc('date_transfert')
            ->sortByDesc('id')
            ->first()?->reminder ?? $client->old_solde;

        $year = (int) date('Y');
        return view('clients.show', compact('client', 'year', 'soldesByYear', 'lastReminder'));
    }

    public function edit(Client $client)
    {
        $vannes = Vannes::orderBy('reference')->get();
        $selectedVannes = $client->vannes->pluck('id')->toArray();
        return view('clients.edit', compact('client', 'vannes', 'selectedVannes'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'cin'         => 'nullable|string|max:8',
            'num_dossier' => 'nullable|string|max:50|unique:client,num_dossier,' . $client->id,
            'tel'         => 'nullable|string|max:50',
            'comments'    => 'nullable|string',
            'gtree'       => 'nullable|integer',
            'mtree'       => 'nullable|integer',
            'otree'       => 'nullable|integer',
            'ptree'       => 'nullable|integer',
            'old_solde'   => 'nullable|numeric',
            'vannes'      => 'nullable|array',
            'vannes.*'    => 'exists:vannes,id',
        ]);

        $client->update([
            'name'        => $data['name'],
            'cin'         => $data['cin'] ?? null,
            'num_dossier' => $data['num_dossier'] ?? null,
            'tel'         => $data['tel'] ?? null,
            'comments'    => $data['comments'] ?? null,
            'gtree'       => $data['gtree'] ?? 0,
            'mtree'       => $data['mtree'] ?? 0,
            'otree'       => $data['otree'] ?? 0,
            'ptree'       => $data['ptree'] ?? 0,
            'old_solde'   => $data['old_solde'] ?? 0,
        ]);

        $client->vannes()->sync($data['vannes'] ?? []);

        return redirect()->route('clients.show', $client)
            ->with('success', 'تم تحديث العميل بنجاح.');
    }

    public function destroy(Client $client)
    {
        $client->vannes()->detach();
        $client->delete();
        return redirect()->route('clients.index')
            ->with('success', 'تم حذف العميل.');
    }
}
