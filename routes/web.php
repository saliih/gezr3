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
use App\Http\Controllers\UserController;
use App\Http\Controllers\VannesController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Clients
    Route::resource('clients', ClientController::class);

    // Vannes
    Route::resource('vannes', VannesController::class);

    // Soldes (paiements - credit)
    Route::get('paiements', [SoldeController::class, 'index'])->defaults('type', 'credit')->name('paiements.index');
    Route::get('paiements/create', [SoldeController::class, 'createPaiement'])->name('paiements.create');
    Route::post('paiements', [SoldeController::class, 'storePaiement'])->name('paiements.store');
    Route::get('paiements/client-data', [SoldeController::class, 'ajaxClientData'])->name('paiements.client-data');
    Route::get('paiements/{solde}/edit', [SoldeController::class, 'edit'])->name('paiements.edit');
    Route::put('paiements/{solde}', [SoldeController::class, 'update'])->name('paiements.update');
    Route::delete('paiements/{solde}', [SoldeController::class, 'destroy'])->name('paiements.destroy');

    // Consommation (debit)
    Route::get('consommation/print', [SoldeController::class, 'print'])->name('consommation.print');
    Route::get('consommation', [SoldeController::class, 'index'])->defaults('type', 'debit')->name('consommation.index');
    Route::get('consommation/create', [SoldeController::class, 'create'])->defaults('type', 'debit')->name('consommation.create');
    Route::post('consommation', [SoldeController::class, 'store'])->defaults('type', 'debit')->name('consommation.store');
    Route::get('consommation/{solde}/edit', [SoldeController::class, 'edit'])->name('consommation.edit');
    Route::put('consommation/{solde}', [SoldeController::class, 'update'])->name('consommation.update');
    Route::delete('consommation/{solde}', [SoldeController::class, 'destroy'])->name('consommation.destroy');

    // Activations
    Route::get('activations', [SoldeController::class, 'index'])->defaults('type', 'activate')->name('activations.index');
    Route::get('activations/create', [SoldeController::class, 'create'])->defaults('type', 'activate')->name('activations.create');
    Route::post('activations', [SoldeController::class, 'store'])->defaults('type', 'activate')->name('activations.store');
    Route::get('activations/{solde}/edit', [SoldeController::class, 'edit'])->name('activations.edit');
    Route::put('activations/{solde}', [SoldeController::class, 'update'])->name('activations.update');
    Route::delete('activations/{solde}', [SoldeController::class, 'destroy'])->name('activations.destroy');

    // Planning
    Route::get('planning/client-vannes', [PlanningController::class, 'clientVannes'])->name('planning.client-vannes');
    Route::get('planning/create', [PlanningController::class, 'create'])->name('planning.create');
    Route::post('planning', [PlanningController::class, 'store'])->name('planning.store');
    Route::post('planning/{solde}/releve', [PlanningController::class, 'updateReleve'])->name('planning.releve');

    // Nouvelle Activation
    Route::get('nouvelle-activation', [NouvelleActivationController::class, 'create'])->name('nouvelle-activation.create');
    Route::post('nouvelle-activation', [NouvelleActivationController::class, 'store'])->name('nouvelle-activation.store');
    Route::get('nouvelle-activation/client-data', [NouvelleActivationController::class, 'ajaxClientData'])->name('nouvelle-activation.client-data');

    // Params
    Route::get('params', [ParamsController::class, 'edit'])->name('params.edit');
    Route::put('params/{id}', [ParamsController::class, 'update'])->name('params.update');

    // Prix M³
    Route::resource('prix-m3', PrixM3Controller::class)->parameters(['prix-m3' => 'prixM3']);

    // Rapport
    Route::get('rapport/statistiques', [RapportController::class, 'statistiques'])->name('rapport.statistiques');

    // Users (admin only)
    Route::resource('users', UserController::class);
});
