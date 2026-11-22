<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NouvelleActivationController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParamsController;
use App\Http\Controllers\PrixM3Controller;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\SoldeController;
use App\Http\Controllers\SoldeHistoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VannesController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
 * Chaque route est protégée par le middleware « perm:module,action » (voir App\Support\Permissions).
 * Les ROLE_ADMIN / ROLE_SUPER_ADMIN passent toujours ; les autres selon les droits réglés par l'admin.
 * NB : les routes « create » doivent être déclarées avant « show » (clients/create vs clients/{client}).
 */
Route::middleware('auth')->group(function () {
    // Le tableau de bord gère lui-même la redirection si l'utilisateur n'y a pas droit
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Clients
    Route::resource('clients', ClientController::class)->only(['create', 'store'])->middleware('perm:clients,create');
    Route::resource('clients', ClientController::class)->only(['edit', 'update'])->middleware('perm:clients,edit');
    Route::resource('clients', ClientController::class)->only(['destroy'])->middleware('perm:clients,delete');
    Route::resource('clients', ClientController::class)->only(['index', 'show'])->middleware('perm:clients,view');

    // Vannes
    Route::resource('vannes', VannesController::class)->only(['create', 'store'])->middleware('perm:vannes,create');
    Route::resource('vannes', VannesController::class)->only(['edit', 'update'])->middleware('perm:vannes,edit');
    Route::resource('vannes', VannesController::class)->only(['destroy'])->middleware('perm:vannes,delete');
    Route::resource('vannes', VannesController::class)->only(['index', 'show'])->middleware('perm:vannes,view');

    // Soldes (paiements - credit)
    Route::middleware('perm:paiements,view')->group(function () {
        Route::get('paiements', [SoldeController::class, 'index'])->defaults('type', 'credit')->name('paiements.index');
    });
    Route::middleware('perm:paiements,create')->group(function () {
        Route::get('paiements/create', [SoldeController::class, 'createPaiement'])->name('paiements.create');
        Route::post('paiements', [SoldeController::class, 'storePaiement'])->name('paiements.store');
        Route::get('paiements/client-data', [SoldeController::class, 'ajaxClientData'])->name('paiements.client-data');
    });
    Route::middleware('perm:paiements,edit')->group(function () {
        Route::get('paiements/{solde}/edit', [SoldeController::class, 'edit'])->name('paiements.edit');
        Route::put('paiements/{solde}', [SoldeController::class, 'update'])->name('paiements.update');
    });
    Route::delete('paiements/{solde}', [SoldeController::class, 'destroy'])->name('paiements.destroy')->middleware('perm:paiements,delete');

    // Consommation (debit)
    Route::middleware('perm:consommation,view')->group(function () {
        Route::get('consommation/print', [SoldeController::class, 'print'])->name('consommation.print');
        Route::get('consommation', [SoldeController::class, 'index'])->defaults('type', 'debit')->name('consommation.index');
    });
    Route::middleware('perm:consommation,create')->group(function () {
        Route::get('consommation/create', [SoldeController::class, 'create'])->defaults('type', 'debit')->name('consommation.create');
        Route::post('consommation', [SoldeController::class, 'store'])->defaults('type', 'debit')->name('consommation.store');
    });
    Route::middleware('perm:consommation,edit')->group(function () {
        Route::get('consommation/{solde}/edit', [SoldeController::class, 'edit'])->name('consommation.edit');
        Route::put('consommation/{solde}', [SoldeController::class, 'update'])->name('consommation.update');
    });
    Route::delete('consommation/{solde}', [SoldeController::class, 'destroy'])->name('consommation.destroy')->middleware('perm:consommation,delete');

    // Activations
    Route::get('activations', [SoldeController::class, 'index'])->defaults('type', 'activate')->name('activations.index')->middleware('perm:activations,view');
    Route::middleware('perm:activations,create')->group(function () {
        Route::get('activations/create', [SoldeController::class, 'create'])->defaults('type', 'activate')->name('activations.create');
        Route::post('activations', [SoldeController::class, 'store'])->defaults('type', 'activate')->name('activations.store');
    });
    Route::middleware('perm:activations,edit')->group(function () {
        Route::get('activations/{solde}/edit', [SoldeController::class, 'edit'])->name('activations.edit');
        Route::put('activations/{solde}', [SoldeController::class, 'update'])->name('activations.update');
    });
    Route::delete('activations/{solde}', [SoldeController::class, 'destroy'])->name('activations.destroy')->middleware('perm:activations,delete');

    // Planning
    Route::get('planning/create', [PlanningController::class, 'create'])->name('planning.create')->middleware('perm:planning,view');
    Route::middleware('perm:planning,create')->group(function () {
        Route::get('planning/client-vannes', [PlanningController::class, 'clientVannes'])->name('planning.client-vannes');
        Route::post('planning/check', [PlanningController::class, 'check'])->name('planning.check');
        Route::post('planning', [PlanningController::class, 'store'])->name('planning.store');
    });
    Route::middleware('perm:planning,edit')->group(function () {
        Route::post('planning/{solde}/releve', [PlanningController::class, 'updateReleve'])->name('planning.releve');
        Route::post('planning/{solde}/comment', [PlanningController::class, 'updateComment'])->name('planning.comment');
    });

    // Nouvelle Activation
    Route::middleware('perm:nouvelle-activation,create')->group(function () {
        Route::get('nouvelle-activation', [NouvelleActivationController::class, 'create'])->name('nouvelle-activation.create');
        Route::post('nouvelle-activation', [NouvelleActivationController::class, 'store'])->name('nouvelle-activation.store');
        Route::get('nouvelle-activation/client-data', [NouvelleActivationController::class, 'ajaxClientData'])->name('nouvelle-activation.client-data');
    });

    // Params
    Route::get('params', [ParamsController::class, 'edit'])->name('params.edit')->middleware('perm:params,view');
    Route::put('params/{id}', [ParamsController::class, 'update'])->name('params.update')->middleware('perm:params,edit');

    // Prix M³
    Route::resource('prix-m3', PrixM3Controller::class)->parameters(['prix-m3' => 'prixM3'])->only(['create', 'store'])->middleware('perm:prix-m3,create');
    Route::resource('prix-m3', PrixM3Controller::class)->parameters(['prix-m3' => 'prixM3'])->only(['edit', 'update'])->middleware('perm:prix-m3,edit');
    Route::resource('prix-m3', PrixM3Controller::class)->parameters(['prix-m3' => 'prixM3'])->only(['destroy'])->middleware('perm:prix-m3,delete');
    Route::resource('prix-m3', PrixM3Controller::class)->parameters(['prix-m3' => 'prixM3'])->only(['index', 'show'])->middleware('perm:prix-m3,view');

    // Rapport
    Route::get('rapport/statistiques', [RapportController::class, 'statistiques'])->name('rapport.statistiques')->middleware('perm:rapport,view');

    // Administration (ROLE_ADMIN / ROLE_SUPER_ADMIN uniquement)
    Route::middleware('admin.only')->group(function () {
        Route::get('historique', [SoldeHistoryController::class, 'index'])->name('historique.index');
        Route::resource('users', UserController::class);
    });
});
