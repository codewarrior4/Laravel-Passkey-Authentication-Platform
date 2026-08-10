<?php

use App\Authentication\Passkeys\Contracts\PasskeyService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/passkeys/lab', function (PasskeyService $passkeyService) {
    return view('passkeys.lab', [
        'featureFlags' => config('passkeys.feature_flags'),
        'relyingParty' => $passkeyService->relyingParty(),
    ]);
})->name('passkeys.lab');
