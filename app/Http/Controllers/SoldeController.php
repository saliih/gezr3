<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Solde;
use App\Models\Vannes;
use Illuminate\Http\Request;

class SoldeController extends Controller
{
    private array $typeConfig = [
        'credit'   => ['label' => 'الدفعات', 'prefix' => 'paiements'],
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
