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
        // Sans droit sur le tableau de bord : première page autorisée, sinon 403
        $user = $request->user();
        if (!$user->canDo('dashboard', 'view')) {
            $route = $user->firstAllowedRoute();
            abort_if(!$route || $route === 'dashboard', 403, 'ليست لديك صلاحية للدخول إلى أي صفحة.');
            return redirect()->route($route);
        }

        $currentYear = (int) date('Y');
        $year        = (int) ($request->input('year', $currentYear));

        $totalClients  = Client::count();
        $totalVannes   = Vannes::count();
        $vannesEnabled = Vannes::where('enable', true)->count();

        $activeClients = ClientActivation::where('year', $year)->count();

        $totalPaiements = Solde::where('type', 'credit')
            ->whereYear('plan_date', $year)
            ->sum('amount');

        $price = PrixM3::where('year', $year)->value('price') ?? 0;

        $totalConsoM3 = Solde::where('type', 'debit')
            ->whereYear('plan_date', $year)
            ->sum('consume');

        $totalConsoVal = round($totalConsoM3 * $price, 2);

        $recentSoldes = Solde::with('client')
            ->whereYear('plan_date', $year)
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
