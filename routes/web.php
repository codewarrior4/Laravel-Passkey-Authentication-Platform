<?php

use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Http\Controllers\PasskeyExperienceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PasskeyExperienceController::class, 'overview'])->name('passkeys.overview');
Route::get('/login', [PasskeyExperienceController::class, 'login'])->name('login');

Route::get('/passkeys/lab', function (PasskeyService $passkeyService) {
    return view('passkeys.lab', [
        'featureFlags' => config('passkeys.feature_flags'),
        'relyingParty' => $passkeyService->relyingParty(),
    ]);
})->name('passkeys.lab');

Route::prefix('passkeys')->group(function (): void {
    Route::get('/', [PasskeyExperienceController::class, 'overview'])->name('passkeys.home');
    Route::get('/register', [PasskeyExperienceController::class, 'register'])->name('passkeys.register');
    Route::post('/register/start', [PasskeyExperienceController::class, 'startRegistration'])->middleware('throttle:passkey-registration')->name('passkeys.register.start');
    Route::post('/register/finish', [PasskeyExperienceController::class, 'finishRegistration'])->name('passkeys.register.finish');
    Route::post('/register/preview', [PasskeyExperienceController::class, 'storeRegistrationPreview'])->name('passkeys.register.preview');
    Route::get('/login', [PasskeyExperienceController::class, 'login'])->name('passkeys.login');
    Route::post('/login/start', [PasskeyExperienceController::class, 'startAuthentication'])->middleware('throttle:passkey-login-start')->name('passkeys.login.start');
    Route::post('/login/finish', [PasskeyExperienceController::class, 'finishAuthentication'])->middleware('throttle:passkey-login-finish')->name('passkeys.login.finish');
    Route::post('/login/preview', [PasskeyExperienceController::class, 'storeLoginPreview'])->name('passkeys.login.preview');
    Route::post('/logout', [PasskeyExperienceController::class, 'logout'])->name('passkeys.logout');
    Route::get('/dashboard', [PasskeyExperienceController::class, 'dashboard'])->middleware(['auth', 'throttle:passkey-device-actions'])->name('passkeys.dashboard');
    Route::post('/devices/{device}/rename', [PasskeyExperienceController::class, 'renameDevice'])->middleware(['auth', 'passkey.reauth', 'throttle:passkey-device-actions'])->name('passkeys.devices.rename');
    Route::post('/devices/{device}/revoke', [PasskeyExperienceController::class, 'revokeDevice'])->middleware(['auth', 'passkey.reauth', 'throttle:passkey-device-actions'])->name('passkeys.devices.revoke');
    Route::post('/passkeys/{passkey}/revoke', [PasskeyExperienceController::class, 'revokePasskey'])->middleware(['auth', 'passkey.reauth', 'throttle:passkey-device-actions'])->name('passkeys.revoke');
});
