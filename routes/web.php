<?php

use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Http\Controllers\PasskeyExperienceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PasskeyExperienceController::class, 'overview'])->name('passkeys.overview');

Route::get('/passkeys/lab', function (PasskeyService $passkeyService) {
    return view('passkeys.lab', [
        'featureFlags' => config('passkeys.feature_flags'),
        'relyingParty' => $passkeyService->relyingParty(),
    ]);
})->name('passkeys.lab');

Route::prefix('passkeys')->group(function (): void {
    Route::get('/', [PasskeyExperienceController::class, 'overview'])->name('passkeys.home');
    Route::get('/register', [PasskeyExperienceController::class, 'register'])->name('passkeys.register');
    Route::post('/register/preview', [PasskeyExperienceController::class, 'storeRegistrationPreview'])->name('passkeys.register.preview');
    Route::get('/login', [PasskeyExperienceController::class, 'login'])->name('passkeys.login');
    Route::post('/login/preview', [PasskeyExperienceController::class, 'storeLoginPreview'])->name('passkeys.login.preview');
    Route::get('/dashboard', [PasskeyExperienceController::class, 'dashboard'])->name('passkeys.dashboard');
});
