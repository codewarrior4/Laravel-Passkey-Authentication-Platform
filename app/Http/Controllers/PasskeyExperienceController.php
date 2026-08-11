<?php

namespace App\Http\Controllers;

use App\Authentication\Passkeys\Actions\PreviewPasskeyAuthenticationAction;
use App\Authentication\Passkeys\Actions\PreviewPasskeyRegistrationAction;
use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\LoginPasskeyPreviewData;
use App\Authentication\Passkeys\DTO\PasskeyPreviewResult;
use App\Authentication\Passkeys\DTO\RegisterPasskeyPreviewData;
use App\Http\Requests\Passkeys\LoginPasskeyPreviewRequest;
use App\Http\Requests\Passkeys\RegisterPasskeyPreviewRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasskeyExperienceController extends Controller
{
    public function overview(PasskeyService $passkeyService): View
    {
        return view('passkeys.overview', $this->buildPageData($passkeyService, [
            'page_title' => 'Passkey Experience',
            'page_eyebrow' => 'Showcase Surface',
            'page_heading' => 'A polished passkey experience for live demos and product reviews.',
            'page_copy' => 'Guide people through registration, login, and the authenticated dashboard from one coherent interface while the deeper WebAuthn backend work continues.',
            'current_route' => 'passkeys.overview',
        ]));
    }

    public function register(PasskeyService $passkeyService): View
    {
        return view('passkeys.register', $this->buildPageData($passkeyService, [
            'page_title' => 'Register Passkey',
            'page_eyebrow' => 'Registration',
            'page_heading' => 'Register a passkey in a flow that feels production-ready.',
            'page_copy' => 'Capture the product story now with a presentable onboarding screen while the real WebAuthn ceremony is wired in behind it.',
            'current_route' => 'passkeys.register',
        ]));
    }

    public function storeRegistrationPreview(
        RegisterPasskeyPreviewRequest $request,
        PreviewPasskeyRegistrationAction $previewPasskeyRegistrationAction,
    ): RedirectResponse {
        $result = $previewPasskeyRegistrationAction->handle(new RegisterPasskeyPreviewData(
            fullName: $request->string('full_name')->toString(),
            workEmail: $request->string('work_email')->toString(),
            deviceName: $request->string('device_name')->toString(),
            ipAddress: $request->ip() ?? '127.0.0.1',
            userAgent: (string) $request->userAgent(),
        ));

        return $this->redirectWithDemoState('passkeys.register', $result);
    }

    public function login(PasskeyService $passkeyService): View
    {
        return view('passkeys.login', $this->buildPageData($passkeyService, [
            'page_title' => 'Passkey Login',
            'page_eyebrow' => 'Authentication',
            'page_heading' => 'Sign in with the fastest path to a secure session.',
            'page_copy' => 'This screen is designed to demo confidence, recovery cues, and device trust while the signature verification flow is being finished.',
            'current_route' => 'passkeys.login',
        ]));
    }

    public function storeLoginPreview(
        LoginPasskeyPreviewRequest $request,
        PreviewPasskeyAuthenticationAction $previewPasskeyAuthenticationAction,
    ): RedirectResponse {
        $result = $previewPasskeyAuthenticationAction->handle(new LoginPasskeyPreviewData(
            workEmail: $request->string('work_email')->toString(),
            deviceChoice: $request->string('device_choice')->toString(),
            ipAddress: $request->ip() ?? '127.0.0.1',
            userAgent: (string) $request->userAgent(),
        ));

        return $this->redirectWithDemoState('passkeys.dashboard', $result);
    }

    public function dashboard(PasskeyService $passkeyService): View
    {
        return view('passkeys.dashboard', $this->buildPageData($passkeyService, [
            'page_title' => 'Security Dashboard',
            'page_eyebrow' => 'Security Center',
            'page_heading' => 'See the account, devices, sessions, and audit story in one place.',
            'page_copy' => 'This dashboard is where device control, revocation, suspicious activity review, and rollout awareness come together for demos and stakeholder walkthroughs.',
            'current_route' => 'passkeys.dashboard',
        ]));
    }

    /**
     * @param  array{page_title: string, page_eyebrow: string, page_heading: string, page_copy: string, current_route: string}  $page
     * @return array<string, mixed>
     */
    private function buildPageData(PasskeyService $passkeyService, array $page): array
    {
        $demoUser = $this->currentDemoUser();

        return [
            ...$page,
            'featureFlags' => config('passkeys.feature_flags'),
            'heroMetrics' => $demoUser === null ? [
                ['label' => 'Passkey adoption target', 'value' => '72%', 'detail' => 'Target for the internal pilot before wider rollout.'],
                ['label' => 'Trusted devices online', 'value' => '12', 'detail' => 'Combination of desktop, mobile, and security-key based access.'],
                ['label' => 'Median sign-in time', 'value' => '8.4s', 'detail' => 'Goal for a fast, low-friction authentication ceremony.'],
            ] : [
                ['label' => 'Registered devices', 'value' => (string) $demoUser->devices->count(), 'detail' => 'Devices currently tied to the active demo account.'],
                ['label' => 'Passkey drafts or credentials', 'value' => (string) $demoUser->passkeys->count(), 'detail' => 'Tuesday registration core now persists passkey records and challenge windows.'],
                ['label' => 'Audit events captured', 'value' => (string) $demoUser->authenticationEvents->count(), 'detail' => 'Security events are now stored in the database for review.'],
            ],
            'navigationItems' => [
                ['label' => 'Overview', 'route' => 'passkeys.overview'],
                ['label' => 'Register', 'route' => 'passkeys.register'],
                ['label' => 'Login', 'route' => 'passkeys.login'],
                ['label' => 'Dashboard', 'route' => 'passkeys.dashboard'],
            ],
            'recentAuditEvents' => $demoUser === null ? [
                ['title' => 'New MacBook Pro registered', 'detail' => 'Product Design team laptop added from San Francisco, CA.', 'time' => '6 minutes ago', 'tone' => 'good'],
                ['title' => 'Authentication challenge created', 'detail' => 'Web session requested from Chrome on macOS.', 'time' => '21 minutes ago', 'tone' => 'neutral'],
                ['title' => 'Recovery option reviewed', 'detail' => 'Backup recovery process viewed by account owner.', 'time' => '2 hours ago', 'tone' => 'neutral'],
                ['title' => 'High-risk login blocked', 'detail' => 'Unknown browser signature rejected after abnormal IP shift.', 'time' => 'Yesterday', 'tone' => 'alert'],
            ] : $demoUser->authenticationEvents
                ->sortByDesc('occurred_at')
                ->take(4)
                ->map(fn ($event) => [
                    'detail' => $event->metadata['device_label'] ?? $event->user_agent ?? 'Security event captured for the active demo account.',
                    'time' => $event->occurred_at?->diffForHumans() ?? 'Just now',
                    'title' => str($event->event)->replace('.', ' ')->headline()->toString(),
                    'tone' => str($event->event)->contains(['failed', 'blocked']) ? 'alert' : 'good',
                ])
                ->values()
                ->all(),
            'recentSessions' => $demoUser === null ? [
                ['location' => 'San Francisco, CA', 'device' => 'Safari on MacBook Pro', 'status' => 'Current session', 'status_tone' => 'good'],
                ['location' => 'Austin, TX', 'device' => 'Chrome on Pixel 10', 'status' => 'Trusted mobile', 'status_tone' => 'neutral'],
                ['location' => 'London, UK', 'device' => 'Security key on shared kiosk', 'status' => 'Ended 2 days ago', 'status_tone' => 'muted'],
            ] : $demoUser->devices
                ->sortByDesc('last_used_at')
                ->take(3)
                ->map(fn ($device) => [
                    'device' => "{$device->browser} on {$device->platform}",
                    'location' => $device->ip_address ?? 'Demo environment',
                    'status' => $device->last_used_at ? 'Recent session' : 'Registered device',
                    'status_tone' => $device->revoked_at ? 'muted' : 'good',
                ])
                ->values()
                ->all(),
            'registeredDevices' => $demoUser === null ? [
                ['name' => 'Executive MacBook', 'type' => 'Platform passkey', 'last_used' => '3 minutes ago', 'trust' => 'Primary', 'trust_tone' => 'good'],
                ['name' => 'Product Demo iPhone', 'type' => 'Phone passkey', 'last_used' => '42 minutes ago', 'trust' => 'Trusted backup', 'trust_tone' => 'neutral'],
                ['name' => 'YubiKey 5 NFC', 'type' => 'Hardware key', 'last_used' => '4 days ago', 'trust' => 'Recovery device', 'trust_tone' => 'muted'],
            ] : $demoUser->devices
                ->sortByDesc('created_at')
                ->take(4)
                ->map(fn ($device) => [
                    'name' => $device->label,
                    'type' => "{$device->type} passkey",
                    'last_used' => $device->last_used_at?->diffForHumans() ?? 'Not used yet',
                    'trust' => $device->revoked_at ? 'Revoked' : 'Active',
                    'trust_tone' => $device->revoked_at ? 'muted' : 'good',
                ])
                ->values()
                ->all(),
            'relyingParty' => $passkeyService->relyingParty(),
            'securitySignals' => [
                ['label' => 'Session hardening', 'state' => 'Ready', 'description' => 'Secure cookie defaults, rotation, and trusted device separation are in scope.'],
                ['label' => 'Risk detection', 'state' => 'In progress', 'description' => 'New-device, impossible-travel, and unusual frequency signals are being staged.'],
                ['label' => 'Instant rollback', 'state' => 'Protected', 'description' => 'Feature flags are keeping release control close to operations.'],
            ],
            'demoUser' => $demoUser,
        ];
    }

    private function currentDemoUser(): ?User
    {
        $demoUserId = session('passkey_demo_user_id');

        if (! is_int($demoUserId) && ! ctype_digit((string) $demoUserId)) {
            return null;
        }

        return User::query()
            ->with(['authenticationEvents', 'devices', 'passkeys'])
            ->find((int) $demoUserId);
    }

    private function redirectWithDemoState(string $route, PasskeyPreviewResult $result): RedirectResponse
    {
        session(['passkey_demo_user_id' => $result->userId]);

        return to_route($route)->with('status', $result->message);
    }
}
