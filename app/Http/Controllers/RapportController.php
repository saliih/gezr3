<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientActivation;
use App\Models\PrixM3;
use App\Models\Solde;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    public function statistiques(Request $request)
    {
        $year = (int) ($request->input('year', date('Y')));

        $totalClients  = Client::count();
        $activeClients = ClientActivation::where('year', $year)->count();
        $inactiveClients = $totalClients - $activeClients;

        $activationFee = PrixM3::where('year', $year)->value('activation_fee') ?? 0;
        $price         = PrixM3::where('year', $year)->value('price') ?? 0;

        $fraisAbonnement = $activeClients * $activationFee;

        $montantCredite = Solde::where('type', 'credit')
            ->whereYear('date_transfert', $year)
            ->sum('amount');

        $totalConsoM3 = Solde::where('type', 'debit')
            ->whereYear('date_transfert', $year)
            ->sum('consume');

        $montantDebite = round($totalConsoM3 * $price, 2);
        $totalMontant  = round((float) $montantCredite + $montantDebite + $fraisAbonnement, 2);

        $years = PrixM3::orderByDesc('year')->pluck('year');

        return view('rapport.statistiques', compact(
            'year', 'years', 'totalClients', 'activeClients', 'inactiveClients',
            'fraisAbonnement', 'montantCredite', 'montantDebite',
            'totalConsoM3', 'totalMontant', 'price', 'activationFee'
        ));
    }
}
