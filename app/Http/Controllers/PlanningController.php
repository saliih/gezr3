<?php

namespace App\Http\Controllers;

use App\Models\PrixM3;
use App\Models\Solde;
use App\Models\Vannes;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function create()
    {
        $vannes  = Vannes::orderBy('reference')->get();
        $year    = (int) date('Y');
        $clients = \App\Models\Client::whereHas('activations', fn($q) => $q->where('year', $year))
                       ->orderBy('name')->get();

        $pending = Solde::with(['client', 'vannes'])
            ->where('type', 'debit')
            ->whereNotNull('plan_date')
            ->where(fn($q) => $q->whereNull('new_counter')->orWhere('new_counter', 0))
            ->orderByDesc('plan_date')
            ->orderBy('vannes_id')
            ->get();

        // auto_number du relevé précédent du même compteur (dernier relevé saisi avant celui-ci)
        $recorded = Solde::where('type', 'debit')
            ->whereIn('vannes_id', $pending->pluck('vannes_id')->filter()->unique())
            ->where('new_counter', '>', 0)
            ->orderBy('id')
            ->get(['id', 'vannes_id', 'auto_number'])
            ->groupBy('vannes_id');

        foreach ($pending as $s) {
            $s->prev_auto_number = $recorded->get($s->vannes_id)
                ?->where('id', '<', $s->id)
                ->last()?->auto_number;
        }

        // Attach prev_reminder for each pending solde
        foreach ($pending as $s) {
            $prev = Solde::where('client_id', $s->client_id)
                ->where('id', '<', $s->id)
                ->orderByDesc('id')
                ->value('reminder');
            $s->prev_reminder = $prev ?? ($s->client?->old_solde ?? 0);
        }

        return view('planning.create', [
            'vannes'      => $vannes,
            'clients'     => $clients,
            'defaultDate' => date('Y-m-d'),
            'pending'     => $pending,
        ]);
    }

    public function clientVannes(Request $request)
    {
        $client = \App\Models\Client::with('vannes')->findOrFail($request->input('client_id'));

        return response()->json([
            'num_dossier' => $client->num_dossier ?? '',
            'vannes'      => $client->vannes->map(fn($v) => [
                'id'          => $v->id,
                'label'       => (string) $v,
                'old_counter' => $v->last_value ?? 0,
            ]),
        ]);
    }

    public function updateReleve(Request $request, Solde $solde)
    {
        $data = $request->validate([
            'old_counter' => 'required|integer|min:0',
            'new_counter' => 'required|integer|min:0',
        ]);

        $old     = (int) $data['old_counter'];
        $new     = (int) $data['new_counter'];
        $consume = ($new > 0) ? max(0, $new - $old) : 0;

        $year  = (int) ($solde->plan_date?->format('Y') ?? date('Y'));
        $prix  = PrixM3::where('year', $year)->first();
        $price = $prix?->price ?? 0;
        $amount = round($consume * $price, 2);

        // Reminder = previous solde reminder for this client minus consume
        $prevSolde    = Solde::where('client_id', $solde->client_id)
            ->where('id', '<', $solde->id)
            ->orderByDesc('id')
            ->first();
        $prevReminder = $prevSolde?->reminder ?? ($solde->client?->old_solde ?? 0);
        $reminder     = round((float) $prevReminder - $consume, 2);

        // Update vanne last_value
        if ($new > 0 && $solde->vannes) {
            $solde->vannes->update(['last_value' => $new]);
        }

        $solde->update([
            'old_counter'    => $old,
            'new_counter'    => $new,
            'consume'        => $consume,
            'amount'         => $amount,
            'reminder'       => $reminder,
            'locked'         => true,
        ]);

        return response()->json([
            'success'  => true,
            'consume'  => $consume,
            'amount'   => number_format($amount, 2),
            'reminder' => number_format($reminder, 2),
        ]);
    }

    /**
     * Lignes valides (client + compteur renseignés) en doublon : soit avec une autre ligne
     * du formulaire, soit avec un relevé déjà en base (même fellah, même compteur, même date).
     * Retourne [['index' => clé de ligne, 'type' => 'form'|'db'], ...]
     */
    private function findConflicts(array $rows): array
    {
        $conflicts = [];
        $seen      = [];

        foreach ($rows as $i => $row) {
            if (empty($row['vannes_id']) || empty($row['client_id'])) {
                continue;
            }

            $date = !empty($row['plan_date']) ? $row['plan_date'] : date('Y-m-d');
            $key  = $row['client_id'] . '|' . $row['vannes_id'] . '|' . $date;

            if (isset($seen[$key])) {
                $conflicts[] = ['index' => $i, 'type' => 'form'];
                continue;
            }
            $seen[$key] = true;

            $exists = Solde::where('type', 'debit')
                ->where('client_id', $row['client_id'])
                ->where('vannes_id', $row['vannes_id'])
                ->whereDate('plan_date', $date)
                ->exists();

            if ($exists) {
                $conflicts[] = ['index' => $i, 'type' => 'db'];
            }
        }

        return $conflicts;
    }

    public function check(Request $request)
    {
        return response()->json([
            'conflicts' => $this->findConflicts($request->input('rows', [])),
        ]);
    }

    public function store(Request $request)
    {
        $rows = $request->input('rows', []);

        // Garde-fou serveur : rien n'est enregistré s'il y a un doublon
        if ($this->findConflicts($rows)) {
            return back()->with('error', 'يوجد سطر مكرر (نفس الفلاح والعداد والتاريخ). لم يتم حفظ أي سطر، صحّح ثم أعد المحاولة.');
        }

        $created = 0;
        foreach ($rows as $row) {
            if (empty($row['vannes_id']) || empty($row['client_id'])) {
                continue;
            }

            $oldCounter = (int) ($row['old_counter'] ?? 0);
            $newRaw     = $row['new_counter'] ?? '';
            $newCounter = ($newRaw !== '' && $newRaw !== null) ? (int) $newRaw : 0;
            $consume    = ($newCounter > 0) ? max(0, $newCounter - $oldCounter) : 0;

            Solde::create([
                'client_id'   => $row['client_id'],
                'vannes_id'   => $row['vannes_id'],
                'type'        => 'debit',
                'old_counter' => $oldCounter,
                'new_counter' => $newCounter,
                'consume'     => $consume,
                'plan_date'   => !empty($row['plan_date']) ? $row['plan_date'] : date('Y-m-d'),
            ]);

            $created++;
        }

        if ($created === 0) {
            return back()->with('warning', 'لم يتم إنشاء أي سطر: اختر الفلاح والعداد في سطر واحد على الأقل.');
        }

        return redirect()->route('consommation.index')
            ->with('success', "تم إنشاء {$created} سطر في التخطيط.");
    }
}
