<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientActivation;
use App\Models\PrixM3;
use App\Models\Solde;
use App\Models\Vannes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SoldeController extends Controller
{
    private array $typeConfig = [
        'credit'   => ['label' => 'مداخيل', 'prefix' => 'paiements'],
        'debit'    => ['label' => 'الاستهلاك', 'prefix' => 'consommation'],
        'activate' => ['label' => 'التفعيلات', 'prefix' => 'activations'],
    ];

    public function index(Request $request, string $type = 'credit')
    {
        $query = Solde::with(['client', 'vannes'])
            ->where('type', $type);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date_transfert', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date_transfert', '<=', $request->input('date_to'));
        }
        if ($request->filled('locked')) {
            $query->where('locked', $request->input('locked') === '1');
        }
        if ($type === 'activate' && $request->filled('year')) {
            $query->whereYear('date_transfert', $request->input('year'));
        }

        $soldes = $query->orderByDesc('id')->paginate(25)->withQueryString();
        $clients = Client::orderBy('name')->get(['id', 'name', 'num_dossier']);
        $config  = $this->typeConfig[$type];

        return view('soldes.index', compact('soldes', 'clients', 'type', 'config'));
    }

    public function create(Request $request, string $type = 'credit')
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'num_dossier']);
        $vannes  = Vannes::orderBy('reference')->get();
        $config  = $this->typeConfig[$type];
        return view('soldes.create', compact('clients', 'vannes', 'type', 'config'));
    }

    public function createPaiement()
    {
        $clients = Client::orderBy('name')->get();
        $year    = (int) date('Y');
        return view('paiements.create', compact('clients', 'year'));
    }

    public function ajaxClientData(Request $request)
    {
        $clientId = $request->integer('client_id');
        $year     = (int) date('Y');
        $prevYear = $year - 1;

        $client = Client::findOrFail($clientId);

        // Remaining balance = reminder of last credit solde of current year for this client
        $lastCredit = Solde::where('client_id', $clientId)
            ->where('type', 'credit')
            ->whereYear('date_transfert', $year)
            ->orderByDesc('date_transfert')
            ->orderByDesc('id')
            ->first();

        $remaining = $lastCredit?->reminder ?? 0;
        $prevPrice = PrixM3::where('year', $prevYear)->first()?->price ?? 0;

        $settlementAmount = ($prevPrice > 0 && $remaining > 0)
            ? round($remaining / $prevPrice, 2)
            : 0;

        $isActive = ClientActivation::where('client_id', $clientId)
            ->where('year', $year)
            ->exists();

        $vannes = $client->vannes()->get(['vannes.id', 'vannes.reference', 'vannes.link'])
            ->map(fn ($v) => ['id' => $v->id, 'label' => (string) $v])
            ->values();

        return response()->json([
            'remaining'  => $remaining,
            'settlement' => $settlementAmount,
            'is_active'  => $isActive,
            'vannes'     => $vannes,
        ]);
    }

    public function storePaiement(Request $request)
    {
        $data = $request->validate([
            'client_id'         => 'required|exists:client,id',
            'date_transfert'    => 'required|date',
            'transfert_number'  => 'nullable|string|max:50',
            'coupon_number'     => 'nullable|string|max:10',
            'vanne_amount'      => 'required|array|min:1',
            'vanne_amount.*'    => 'required|numeric|min:0',
        ]);

        $year     = (int) date('Y');
        $prevYear = $year - 1;
        $clientId = (int) $data['client_id'];
        $date     = Carbon::parse($data['date_transfert']);

        $client         = Client::findOrFail($clientId);
        $clientVanneIds = $client->vannes()->pluck('vannes.id')->all();

        $vanneAmounts = [];
        foreach ($data['vanne_amount'] as $vanneId => $amount) {
            $vanneId = (int) $vanneId;
            if (!in_array($vanneId, $clientVanneIds, true)) {
                abort(422, 'Vanne non affectée à ce client.');
            }
            $vanneAmounts[$vanneId] = (float) $amount;
        }

        // Remaining balance = reminder of last credit solde of current year for this client
        $lastCredit = Solde::where('client_id', $clientId)
            ->where('type', 'credit')
            ->whereYear('date_transfert', $year)
            ->orderByDesc('date_transfert')
            ->orderByDesc('id')
            ->first();

        $remaining = $lastCredit?->reminder ?? 0;

        $prevPrice = PrixM3::where('year', $prevYear)->first()?->price ?? 0;
        $currPrice = PrixM3::where('year', $year)->first()?->price ?? 0;

        $settlementAmount = ($prevPrice > 0 && $remaining > 0)
            ? round($remaining / $prevPrice, 2)
            : 0;

        // If previous year has remaining balance > 0 → close previous year
        if ($remaining > 0) {
            Solde::create([
                'client_id'        => $clientId,
                'date_transfert'   => Carbon::create($prevYear, 12, 31),
                'type'             => 'credit',
                'amount'           => $settlementAmount,
                'transfert_number' => $data['transfert_number'] ?? null,
                'coupon_number'    => $data['coupon_number'] ?? null,
                'reminder'         => 0,
            ]);
        }

        // المبلغ → reparti par vanne, une ligne de solde par vanne affectée au client
        foreach ($vanneAmounts as $vanneId => $vanneAmount) {
            $reminder = ($currPrice > 0) ? round($vanneAmount / $currPrice, 2) : 0;

            Solde::create([
                'client_id'        => $clientId,
                'vannes_id'        => $vanneId,
                'date_transfert'   => $date,
                'type'             => 'credit',
                'amount'           => $vanneAmount,
                'transfert_number' => $data['transfert_number'] ?? null,
                'coupon_number'    => $data['coupon_number'] ?? null,
                'reminder'         => $reminder,
            ]);
        }

        return redirect()->route('paiements.index')
            ->with('success', 'تم إضافة الدفعة بنجاح.');
    }

    public function store(Request $request, string $type = 'credit')
    {
        $rules = [
            'client_id'        => 'required|exists:client,id',
            'date_transfert'   => 'nullable|date',
            'transfert_number' => 'nullable|string|max:50',
            'coupon_number'    => 'nullable|string|max:10',
        ];

        if ($type === 'credit') {
            $rules['amount'] = 'required|numeric|min:0';
        }
        if ($type === 'debit') {
            $rules['vannes_id']   = 'nullable|exists:vannes,id';
            $rules['old_counter'] = 'nullable|integer';
            $rules['new_counter'] = 'nullable|integer|gte:old_counter';
            $rules['plan_date']   = 'nullable|date';
        }

        $data = $request->validate($rules);
        $data['type'] = $type;

        if ($type === 'debit') {
            $old = (int) ($data['old_counter'] ?? 0);
            $new = (int) ($data['new_counter'] ?? 0);
            $data['consume'] = max(0, $new - $old);
        }

        Solde::create($data);

        $config = $this->typeConfig[$type];
        return redirect()->route($config['prefix'] . '.index')
            ->with('success', 'تم الإضافة بنجاح.');
    }

    public function edit(Solde $solde)
    {
        $type    = $solde->type;
        $clients = Client::orderBy('name')->get(['id', 'name', 'num_dossier']);
        $vannes  = Vannes::orderBy('reference')->get();
        $config  = $this->typeConfig[$type] ?? $this->typeConfig['credit'];
        return view('soldes.edit', compact('solde', 'clients', 'vannes', 'type', 'config'));
    }

    public function update(Request $request, Solde $solde)
    {
        $type  = $solde->type;
        $rules = [
            'client_id'        => 'required|exists:client,id',
            'date_transfert'   => 'nullable|date',
            'transfert_number' => 'nullable|string|max:50',
            'coupon_number'    => 'nullable|string|max:10',
            'locked'           => 'nullable|boolean',
        ];

        if ($type === 'credit') {
            $rules['amount'] = 'required|numeric|min:0';
        }
        if ($type === 'debit') {
            $rules['vannes_id']   = 'nullable|exists:vannes,id';
            $rules['old_counter'] = 'nullable|integer';
            $rules['new_counter'] = 'nullable|integer';
            $rules['plan_date']   = 'nullable|date';
        }

        $data = $request->validate($rules);
        $data['locked'] = $request->boolean('locked');

        if ($type === 'debit') {
            $old = (int) ($data['old_counter'] ?? 0);
            $new = (int) ($data['new_counter'] ?? 0);
            $data['consume'] = max(0, $new - $old);

            // Update vanne last_value with new_counter
            if ($new > 0 && $solde->vannes) {
                $solde->vannes->update(['last_value' => $new]);
            }
        }

        $solde->update($data);

        $config = $this->typeConfig[$type] ?? $this->typeConfig['credit'];
        return redirect()->route($config['prefix'] . '.index')
            ->with('success', 'تم التحديث بنجاح.');
    }

    public function print(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        $soldes = Solde::with(['client', 'vannes'])
            ->where('type', 'debit')
            ->where('plan_date', $date)
            ->where(fn($q) => $q->whereNull('new_counter')->orWhere('new_counter', 0))
            ->orderBy('plan_date')
            ->get();

        return view('soldes.print', compact('soldes', 'date'));
    }

    public function destroy(Solde $solde)
    {
        $type   = $solde->type;
        $config = $this->typeConfig[$type] ?? $this->typeConfig['credit'];
        $solde->delete();
        return redirect()->route($config['prefix'] . '.index')
            ->with('success', 'تم الحذف بنجاح.');
    }
}
