<?php

namespace App\Http\Controllers;

use App\Models\SoldeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SoldeHistoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (!Schema::hasTable('solde_history')) {
            return view('historique.index', [
                'tableReady' => false,
                'history'    => collect(),
                'usernames'  => collect(),
            ]);
        }

        $query = SoldeHistory::with(['client', 'vannes']);

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('username')) {
            $query->where('username', $request->input('username'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('solde_id')) {
            $query->where('solde_id', $request->integer('solde_id'));
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from') . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        $history   = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(50)->withQueryString();
        $usernames = SoldeHistory::whereNotNull('username')->distinct()->orderBy('username')->pluck('username');

        return view('historique.index', ['tableReady' => true, 'history' => $history, 'usernames' => $usernames]);
    }
}
