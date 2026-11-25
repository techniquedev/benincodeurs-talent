<?php

use App\Http\Controllers\AccountController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use Illuminate\Support\Facades\Route;

Route::get('/', [AccountController::class, 'home'])->name('home');

// Invités
Route::middleware('guest')->group(function () {
    Route::get('/inscription', Register::class)->name('register');
    Route::get('/connexion', Login::class)->name('login');
    Route::get('/mot-de-passe-oublie', ForgotPassword::class)->name('password.request');
    Route::get('/reinitialiser-mot-de-passe/{token}', ResetPassword::class)->name('password.reset');
});

// Membres connectés
Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [AccountController::class, 'logout'])->name('logout');

    Route::get('/email/verification', [AccountController::class, 'verifyNotice'])->name('verification.notice');
    Route::get('/email/verification/{id}/{hash}', [AccountController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification/renvoyer', [AccountController::class, 'resendVerification'])
        ->middleware('throttle:6,1')->name('verification.send');

    Route::middleware('verified')->group(function () {
        Route::get('/espace', [AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/compte/en-attente', [AccountController::class, 'pending'])->name('account.pending');

        // Réservé aux comptes validés.
        Route::middleware('approved')->group(function () {
            Route::get('/annuaire', [AccountController::class, 'directory'])->name('directory');
        });
    });
});
