<?php

use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Http\Controllers\PasskeyExperienceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PasskeyExperienceController::class, 'overview'])->name('passkeys.overview');
Route::get('/login', [PasskeyExperienceController::class, 'login'])->middleware('passkey.feature:login')->name('login');

Route::get('/passkeys/lab', function (PasskeyService $passkeyService) {
    return view('passkeys.lab', [
        'featureFlags' => config('passkeys.feature_flags'),
        'relyingParty' => $passkeyService->relyingParty(),
    ]);
})->name('passkeys.lab');

Route::prefix('passkeys')->group(function (): void {
    Route::get('/', [PasskeyExperienceController::class, 'overview'])->name('passkeys.home');
    Route::get('/register', [PasskeyExperienceController::class, 'register'])->middleware('passkey.feature:registration')->name('passkeys.register');
    Route::post('/register/start', [PasskeyExperienceController::class, 'startRegistration'])->middleware(['passkey.feature:registration', 'throttle:passkey-registration'])->name('passkeys.register.start');
    Route::post('/register/finish', [PasskeyExperienceController::class, 'finishRegistration'])->middleware('passkey.feature:registration')->name('passkeys.register.finish');
    Route::post('/register/preview', [PasskeyExperienceController::class, 'storeRegistrationPreview'])->middleware('passkey.feature:registration')->name('passkeys.register.preview');
    Route::get('/login', [PasskeyExperienceController::class, 'login'])->middleware('passkey.feature:login')->name('passkeys.login');
    Route::post('/login/start', [PasskeyExperienceController::class, 'startAuthentication'])->middleware(['passkey.feature:login', 'throttle:passkey-login-start'])->name('passkeys.login.start');
    Route::post('/login/finish', [PasskeyExperienceController::class, 'finishAuthentication'])->middleware(['passkey.feature:login', 'throttle:passkey-login-finish'])->name('passkeys.login.finish');
    Route::post('/login/preview', [PasskeyExperienceController::class, 'storeLoginPreview'])->middleware('passkey.feature:login')->name('passkeys.login.preview');
    Route::post('/logout', [PasskeyExperienceController::class, 'logout'])->name('passkeys.logout');
    Route::get('/dashboard', [PasskeyExperienceController::class, 'dashboard'])->middleware(['auth', 'passkey.feature:enabled', 'throttle:passkey-device-actions'])->name('passkeys.dashboard');
    Route::post('/devices/{device}/rename', [PasskeyExperienceController::class, 'renameDevice'])->middleware(['auth', 'passkey.feature:device_management', 'passkey.reauth', 'throttle:passkey-device-actions'])->name('passkeys.devices.rename');
    Route::post('/devices/{device}/revoke', [PasskeyExperienceController::class, 'revokeDevice'])->middleware(['auth', 'passkey.feature:device_management', 'passkey.reauth', 'throttle:passkey-device-actions'])->name('passkeys.devices.revoke');
    Route::post('/passkeys/{passkey}/revoke', [PasskeyExperienceController::class, 'revokePasskey'])->middleware(['auth', 'passkey.feature:device_management', 'passkey.reauth', 'throttle:passkey-device-actions'])->name('passkeys.revoke');
    Route::post('/sessions/{session}/revoke', [PasskeyExperienceController::class, 'revokeSession'])->middleware(['auth', 'passkey.feature:enabled', 'passkey.reauth', 'throttle:passkey-device-actions'])->name('passkeys.sessions.revoke');
});
