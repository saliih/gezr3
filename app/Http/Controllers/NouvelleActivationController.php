<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientActivation;
use App\Models\PrixM3;
use App\Models\Solde;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NouvelleActivationController extends Controller
{
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $year    = (int) date('Y');
        return view('nouvelle-activation.create', compact('clients', 'year'));
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

        $prevPrix  = PrixM3::where('year', $prevYear)->first();
        $currPrix  = PrixM3::where('year', $year)->first();

        $prevPrice      = $prevPrix?->price ?? 0;
        $activationFee  = $currPrix?->activation_fee ?? 0;

        // Settlement = remaining / prev_price (as requested)
        $settlementAmount = ($prevPrice > 0 && $remaining > 0)
            ? round($remaining / $prevPrice, 2)
            : 0;

        $isAlreadyActive = ClientActivation::where('client_id', $clientId)
            ->where('year', $year)
            ->exists();

        $vannes = $client->vannes()->get(['vannes.id', 'vannes.reference', 'vannes.link'])
            ->map(fn ($v) => ['id' => $v->id, 'label' => (string) $v])
            ->values();

        return response()->json([
            'remaining'        => $remaining,
            'prev_price'       => $prevPrice,
            'settlement'       => $settlementAmount,
            'activation_fee'   => $activationFee,
            'is_already_active'=> $isAlreadyActive,
            'vannes'           => $vannes,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'         => 'required|exists:client,id',
            'date_transfert'    => 'required|date',
            'transfert_number'  => 'nullable|string|max:50',
            'coupon_number'     => 'nullable|string|max:10',
            'vanne_amount'      => 'required|array|min:1',
            'vanne_amount.*'    => 'required|numeric|min:0',
        ]);

        $year    = (int) date('Y');
        $prevYear = $year - 1;
        $clientId = (int) $data['client_id'];
        $date     = Carbon::parse($data['date_transfert']);

        $client = Client::findOrFail($clientId);
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

        $prevPrix  = PrixM3::where('year', $prevYear)->first();
        $currPrix  = PrixM3::where('year', $year)->first();

        $prevPrice     = $prevPrix?->price ?? 0;
        $activationFee = $currPrix?->activation_fee ?? 0;

        $settlementAmount = ($prevPrice > 0 && $remaining > 0)
            ? round($remaining / $prevPrice, 2)
            : 0;

        // 1. Activate client for current year
        ClientActivation::firstOrCreate([
            'client_id' => $clientId,
            'year'      => $year,
        ]);

        // 2. Solde type='activate' for activation fee
        Solde::create([
            'client_id'        => $clientId,
            'date_transfert'   => $date,
            'type'             => 'activate',
            'amount'           => $activationFee,
            'transfert_number' => $data['transfert_number'] ?? null,
            'coupon_number'    => $data['coupon_number'] ?? null,
            'reminder'         => 0,
        ]);

        // 3. If previous year has remaining balance > 0 → close previous year
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

        // 4. المبلغ → reparti par vanne, une ligne de solde par vanne affectée au client
        $currPrice = $currPrix?->price ?? 0;

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

        return redirect()->route('activations.index')
            ->with('success', 'تم إضافة التفعيل بنجاح.');
    }
}
