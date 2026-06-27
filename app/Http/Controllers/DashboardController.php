<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientActivation;
use App\Models\Solde;
use App\Models\Vannes;
use App\Models\PrixM3;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = (int) date('Y');
        $year        = (int) ($request->input('year', $currentYear));

        $totalClients  = Client::count();
        $totalVannes   = Vannes::count();
        $vannesEnabled = Vannes::where('enable', true)->count();

        $activeClients = ClientActivation::where('year', $year)->count();

        $totalPaiements = Solde::where('type', 'credit')
            ->whereYear('date_transfert', $year)
            ->sum('amount');

        $price = PrixM3::where('year', $year)->value('price') ?? 0;

        $totalConsoM3 = Solde::where('type', 'debit')
            ->whereYear('date_transfert', $year)
            ->sum('consume');

        $totalConsoVal = round($totalConsoM3 * $price, 2);

        $recentSoldes = Solde::with('client')
            ->whereYear('date_transfert', $year)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $availableYears = PrixM3::orderByDesc('year')->pluck('year');

        return view('dashboard', compact(
            'totalClients', 'totalVannes', 'activeClients',
            'totalPaiements', 'totalConsoM3', 'totalConsoVal',
            'recentSoldes', 'year', 'currentYear',
            'vannesEnabled', 'availableYears'
        ));
    }
}
