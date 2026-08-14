<?php

namespace App\Http\Controllers;

use App\Authentication\Passkeys\Actions\FinishPasskeyAuthenticationAction;
use App\Authentication\Passkeys\Actions\FinishPasskeyRegistrationAction;
use App\Authentication\Passkeys\Actions\ListActiveSessionsAction;
use App\Authentication\Passkeys\Actions\PreviewPasskeyAuthenticationAction;
use App\Authentication\Passkeys\Actions\PreviewPasskeyRegistrationAction;
use App\Authentication\Passkeys\Actions\RenameDeviceAction;
use App\Authentication\Passkeys\Actions\RevokeDeviceAction;
use App\Authentication\Passkeys\Actions\RevokePasskeyAction;
use App\Authentication\Passkeys\Actions\RevokeSessionAction;
use App\Authentication\Passkeys\Actions\StartBrowserPasskeyAuthenticationAction;
use App\Authentication\Passkeys\Actions\StartBrowserPasskeyRegistrationAction;
use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\FinishPasskeyAuthenticationData;
use App\Authentication\Passkeys\DTO\FinishPasskeyRegistrationData;
use App\Authentication\Passkeys\DTO\LoginPasskeyPreviewData;
use App\Authentication\Passkeys\DTO\PasskeyPreviewResult;
use App\Authentication\Passkeys\DTO\RegisterPasskeyPreviewData;
use App\Authentication\Passkeys\DTO\StartPasskeyAuthenticationData;
use App\Authentication\Passkeys\Exceptions\PasskeyException;
use App\Authentication\Passkeys\Services\SecurityRiskEvaluator;
use App\Http\Requests\Passkeys\FinishPasskeyAuthenticationRequest;
use App\Http\Requests\Passkeys\FinishPasskeyRegistrationRequest;
use App\Http\Requests\Passkeys\LoginPasskeyPreviewRequest;
use App\Http\Requests\Passkeys\RegisterPasskeyPreviewRequest;
use App\Http\Requests\Passkeys\RenameDeviceRequest;
use App\Http\Requests\Passkeys\StartPasskeyAuthenticationRequest;
use App\Http\Requests\Passkeys\StartPasskeyRegistrationRequest;
use App\Models\AuthenticationEvent;
use App\Models\Device;
use App\Models\Passkey;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasskeyExperienceController extends Controller
{
    public function __construct(private readonly ListActiveSessionsAction $listActiveSessionsAction) {}

    public function overview(PasskeyService $passkeyService): View
    {
        return view('passkeys.overview', $this->buildPageData($passkeyService, [
            'page_title' => 'Passkey Experience',
            'page_eyebrow' => 'Showcase Surface',
            'page_heading' => 'A polished passkey experience for live demos and product reviews.',
            'page_copy' => 'Move from enrollment to sign-in to a protected account experience in one clear flow.',
            'current_route' => 'passkeys.overview',
        ]));
    }

    public function register(PasskeyService $passkeyService): View
    {
        return view('passkeys.register', $this->buildPageData($passkeyService, [
            'page_title' => 'Register Passkey',
            'page_eyebrow' => 'Registration',
            'page_heading' => 'Register a passkey in a flow that feels production-ready.',
            'page_copy' => 'A calm, focused setup flow for creating a passkey without exposing protected account data.',
            'current_route' => 'passkeys.register',
        ]));
    }

    public function startRegistration(
        StartPasskeyRegistrationRequest $request,
        StartBrowserPasskeyRegistrationAction $startBrowserPasskeyRegistrationAction,
    ): JsonResponse {
        $ceremony = $startBrowserPasskeyRegistrationAction->handle(new RegisterPasskeyPreviewData(
            fullName: $request->string('full_name')->toString(),
            workEmail: $request->string('work_email')->toString(),
            deviceName: $request->string('device_name')->toString(),
            ipAddress: $request->ip() ?? '127.0.0.1',
            userAgent: (string) $request->userAgent(),
        ));

        return response()->json([
            'passkey_id' => $ceremony->passkeyId,
            'public_key' => $ceremony->options,
        ]);
    }

    public function finishRegistration(
        FinishPasskeyRegistrationRequest $request,
        FinishPasskeyRegistrationAction $finishPasskeyRegistrationAction,
    ): JsonResponse {
        try {
            $passkey = $finishPasskeyRegistrationAction->handle(new FinishPasskeyRegistrationData(
                passkeyId: (int) $request->integer('passkey_id'),
                credentialId: $request->string('credential_id')->toString(),
                clientDataJson: $request->string('client_data_json')->toString(),
                authenticatorData: $request->string('authenticator_data')->toString(),
                publicKey: $request->string('public_key')->toString(),
                publicKeyAlgorithm: (int) $request->integer('public_key_algorithm'),
                transports: $request->array('transports'),
                origin: $request->string('origin')->toString(),
            ));
        } catch (PasskeyException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => "Passkey registration completed for {$passkey->label}.",
            'redirect_to' => route('passkeys.login'),
        ]);
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
            'page_copy' => 'A clean sign-in surface that reveals nothing sensitive until authentication succeeds.',
            'current_route' => 'passkeys.login',
        ]));
    }

    public function startAuthentication(
        StartPasskeyAuthenticationRequest $request,
        StartBrowserPasskeyAuthenticationAction $startBrowserPasskeyAuthenticationAction,
    ): JsonResponse {
        $ceremony = $startBrowserPasskeyAuthenticationAction->handle(new StartPasskeyAuthenticationData(
            email: null,
            ipAddress: $request->ip() ?? '127.0.0.1',
            userAgent: (string) $request->userAgent(),
        ));

        session([
            'passkey_authentication_challenge' => $ceremony->options['challenge'],
            'passkey_authentication_expires_at' => now()->addMinutes(5)->toIso8601String(),
        ]);

        return response()->json([
            'passkey_id' => $ceremony->passkeyId,
            'public_key' => $ceremony->options,
        ]);
    }

    public function finishAuthentication(
        FinishPasskeyAuthenticationRequest $request,
        FinishPasskeyAuthenticationAction $finishPasskeyAuthenticationAction,
        AuthenticationAudit $audit,
        SecurityRiskEvaluator $securityRiskEvaluator,
    ): JsonResponse {
        try {
            $user = $finishPasskeyAuthenticationAction->handle(new FinishPasskeyAuthenticationData(
                credentialId: $request->string('credential_id')->toString(),
                clientDataJson: $request->string('client_data_json')->toString(),
                authenticatorData: $request->string('authenticator_data')->toString(),
                signature: $request->string('signature')->toString(),
                origin: $request->string('origin')->toString(),
            ), $request);
        } catch (PasskeyException $exception) {
            $passkey = Passkey::query()
                ->with('device')
                ->where('credential_id_hash', hash('sha256', $request->string('credential_id')->toString()))
                ->first();

            $audit->record('passkey.authentication.failed', $passkey?->user_id, [
                'device_id' => $passkey?->device_id,
                'failure_reason' => $exception->getMessage(),
                'ip_address' => $request->ip(),
                'passkey_id' => $passkey?->id,
                'risk_level' => 'medium',
                'user_agent' => (string) $request->userAgent(),
            ]);

            $riskScore = $securityRiskEvaluator->evaluate($passkey, $request, failedAttempt: true);

            if ($riskScore->isElevated()) {
                $audit->record('suspicious.activity.detected', $passkey?->user_id, [
                    'device_id' => $passkey?->device_id,
                    'ip_address' => $request->ip(),
                    'passkey_id' => $passkey?->id,
                    'risk_level' => $riskScore->level,
                    'signals' => $riskScore->signals,
                    'user_agent' => (string) $request->userAgent(),
                ]);
            }

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $request->session()->put('passkey_last_authenticated_at', now()->toIso8601String());

        return response()->json([
            'message' => "Passkey login completed for {$user->email}.",
            'redirect_to' => route('passkeys.dashboard'),
        ]);
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

        auth()->loginUsingId($result->userId);
        $request->session()->regenerate();
        $request->session()->put('passkey_last_authenticated_at', now()->toIso8601String());

        return $this->redirectWithDemoState('passkeys.dashboard', $result);
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('passkeys.login')->with('status', 'You have been signed out of the passkey dashboard.');
    }

    public function renameDevice(
        RenameDeviceRequest $request,
        Device $device,
        RenameDeviceAction $renameDeviceAction,
    ): RedirectResponse {
        $user = $this->currentAuthenticatedUser();

        abort_unless($user instanceof User, 403);

        $renameDeviceAction->handle(
            $device,
            $request->string('label')->toString(),
            $user,
            $request->ip() ?? '127.0.0.1',
            (string) $request->userAgent(),
        );

        return to_route('passkeys.dashboard')->with('status', 'Device renamed successfully.');
    }

    public function revokeDevice(
        Request $request,
        Device $device,
        RevokeDeviceAction $revokeDeviceAction,
    ): RedirectResponse {
        $user = $this->currentAuthenticatedUser();

        abort_unless($user instanceof User, 403);
        abort_unless($device->user_id === $user->id, 404);

        $revokeDeviceAction->handle(
            $device,
            $user,
            $request->ip() ?? '127.0.0.1',
            (string) $request->userAgent(),
        );

        return to_route('passkeys.dashboard')->with('status', 'Device revoked and linked passkeys disabled.');
    }

    public function revokePasskey(
        Request $request,
        Passkey $passkey,
        RevokePasskeyAction $revokePasskeyAction,
    ): RedirectResponse {
        $user = $this->currentAuthenticatedUser();

        abort_unless($user instanceof User, 403);
        abort_unless($passkey->user_id === $user->id, 404);

        $revokePasskeyAction->handle(
            $passkey,
            $user,
            $request->ip() ?? '127.0.0.1',
            (string) $request->userAgent(),
        );

        return to_route('passkeys.dashboard')->with('status', 'Passkey removed from this account.');
    }

    public function revokeSession(
        Request $request,
        string $session,
        RevokeSessionAction $revokeSessionAction,
    ): RedirectResponse {
        $user = $this->currentAuthenticatedUser();

        abort_unless($user instanceof User, 403);

        $revoked = $revokeSessionAction->handle(
            $session,
            $request->session()->getId(),
            $user,
            $request->ip() ?? '127.0.0.1',
            (string) $request->userAgent(),
        );

        return to_route('passkeys.dashboard')->with(
            'status',
            $revoked ? 'Selected session ended successfully.' : 'Current session stays active. Use Sign out to end this browser session.',
        );
    }

    public function dashboard(PasskeyService $passkeyService): View
    {
        return view('passkeys.dashboard', $this->buildPageData($passkeyService, [
            'page_title' => 'Security Dashboard',
            'page_eyebrow' => 'Security Center',
            'page_heading' => 'See the account, devices, sessions, and audit story in one place.',
            'page_copy' => 'A protected view for trusted devices, active sessions, and account activity.',
            'current_route' => 'passkeys.dashboard',
        ]));
    }

    /**
     * @param  array{page_title: string, page_eyebrow: string, page_heading: string, page_copy: string, current_route: string}  $page
     * @return array<string, mixed>
     */
    private function buildPageData(PasskeyService $passkeyService, array $page): array
    {
        $isProtectedPage = $page['current_route'] === 'passkeys.dashboard';
        $isPublicPage = in_array($page['current_route'], ['passkeys.overview', 'passkeys.login', 'passkeys.register'], true);
        $focusUser = $isProtectedPage ? $this->currentAuthenticatedUser() : null;

        $activePasskeysCount = Passkey::query()
            ->when($focusUser instanceof User, fn ($query) => $query->where('user_id', $focusUser->id))
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->count();

        $pendingPasskeysCount = Passkey::query()
            ->when($focusUser instanceof User, fn ($query) => $query->where('user_id', $focusUser->id))
            ->where('status', 'pending')
            ->whereNull('revoked_at')
            ->count();

        $revokedDevicesCount = Device::query()
            ->when($focusUser instanceof User, fn ($query) => $query->where('user_id', $focusUser->id))
            ->whereNotNull('revoked_at')
            ->count();

        $latestAuthenticationEvent = AuthenticationEvent::query()
            ->when($focusUser instanceof User, fn ($query) => $query->where('user_id', $focusUser->id))
            ->latest('occurred_at')
            ->value('occurred_at');

        $deviceCollection = $isProtectedPage && $focusUser instanceof User
            ? $focusUser->devices->load('passkeys')
            : collect();

        $registeredDevices = $deviceCollection
            ->map(fn (Device $device): array => [
                'id' => $device->id,
                'name' => $device->label,
                'type' => ucfirst($device->type ?: 'platform').' device',
                'last_used' => $device->last_used_at?->diffForHumans() ?? 'Not used yet',
                'created_at' => $device->created_at?->format('M j, Y') ?? 'Unknown',
                'passkeys_count' => $device->passkeys->count(),
                'trust' => $device->revoked_at ? 'Revoked' : 'Active',
                'trust_tone' => $device->revoked_at ? 'muted' : 'good',
            ])
            ->values()
            ->all();

        $registeredPasskeys = $isProtectedPage && $focusUser !== null
            ? $focusUser->passkeys
                ->sortByDesc('created_at')
                ->map(fn (Passkey $passkey): array => [
                    'id' => $passkey->id,
                    'device' => $passkey->device?->label ?? 'Unknown device',
                    'label' => $passkey->label,
                    'last_used' => $passkey->last_used_at?->diffForHumans() ?? 'Not used yet',
                    'status' => $passkey->revoked_at ? 'Revoked' : ucfirst($passkey->status),
                    'status_tone' => $passkey->revoked_at ? 'muted' : 'good',
                ])
                ->values()
                ->all()
            : [];

        $recentSessions = $isProtectedPage && $focusUser instanceof User
            ? $this->listActiveSessionsAction
                ->handle(
                    $focusUser,
                    request()->session()->getId(),
                    request()->ip() ?? '127.0.0.1',
                    (string) request()->userAgent(),
                )
                ->values()
                ->all()
            : [];

        $recentAuditEvents = AuthenticationEvent::query()
            ->with(['device', 'user'])
            ->when($focusUser instanceof User, fn ($query) => $query->where('user_id', $focusUser->id))
            ->latest('occurred_at')
            ->take(6)
            ->get()
            ->map(fn (AuthenticationEvent $event): array => [
                'detail' => $event->device?->label
                    ?? $event->ip_address
                    ?? 'Security event captured.',
                'time' => $event->occurred_at?->diffForHumans() ?? 'Just now',
                'title' => str($event->event)->replace('.', ' ')->headline()->toString(),
                'tone' => str($event->event)->contains(['failed', 'blocked', 'revoked']) ? 'alert' : 'good',
            ])
            ->values()
            ->all();

        $auditEventsCount = AuthenticationEvent::query()
            ->when($focusUser instanceof User, fn ($query) => $query->where('user_id', $focusUser->id))
            ->count();

        $heroMetrics = $isProtectedPage
            ? [
                [
                    'label' => 'Devices',
                    'value' => (string) count($registeredDevices),
                    'detail' => 'Trusted devices currently linked to this account.',
                ],
                [
                    'label' => 'Active passkeys',
                    'value' => (string) $activePasskeysCount,
                    'detail' => 'Credentials available for secure sign-in on this account.',
                ],
                [
                    'label' => 'Active sessions',
                    'value' => (string) count($recentSessions),
                    'detail' => count($recentSessions) === 0
                        ? 'No active browser sessions are stored yet.'
                        : 'Current and recent browser sessions for this account.',
                ],
                [
                    'label' => 'Audit events',
                    'value' => (string) $auditEventsCount,
                    'detail' => ! $latestAuthenticationEvent instanceof CarbonInterface
                        ? 'No security events have been captured yet.'
                        : 'Latest security event '.$latestAuthenticationEvent->diffForHumans().'.',
                ],
            ]
            : [];

        $featureFlags = collect(config('passkeys.feature_flags'))
            ->map(fn (array $feature): array => [
                'active' => (bool) ($feature['active'] ?? false),
                'key' => (string) ($feature['key'] ?? ''),
                'label' => (string) ($feature['label'] ?? 'Feature'),
            ])
            ->values()
            ->all();

        return [
            ...$page,
            'featureFlags' => $featureFlags,
            'heroMetrics' => $heroMetrics,
            'navigationItems' => [
                ['label' => 'Overview', 'route' => 'passkeys.overview'],
                ['label' => 'Register', 'route' => 'passkeys.register'],
                ['label' => 'Login', 'route' => 'passkeys.login'],
                ['label' => 'Dashboard', 'route' => 'passkeys.dashboard'],
            ],
            'recentAuditEvents' => $recentAuditEvents,
            'recentSessions' => $recentSessions,
            'registeredDevices' => $registeredDevices,
            'registeredPasskeys' => $registeredPasskeys,
            'relyingParty' => $passkeyService->relyingParty(),
            'securitySignals' => [
                [
                    'label' => 'Active credentials',
                    'state' => (string) $activePasskeysCount,
                    'description' => 'Passkeys currently marked active and available for sign-in.',
                ],
                [
                    'label' => 'Pending registrations',
                    'state' => (string) $pendingPasskeysCount,
                    'description' => 'Registration drafts waiting for a browser ceremony to finish.',
                ],
                [
                    'label' => 'Revoked devices',
                    'state' => (string) $revokedDevicesCount,
                    'description' => 'Devices removed from trust but still preserved in the audit trail.',
                ],
                [
                    'label' => 'Risk alerts',
                    'state' => (string) AuthenticationEvent::query()
                        ->when($focusUser instanceof User, fn ($query) => $query->where('user_id', $focusUser->id))
                        ->where('event', 'suspicious.activity.detected')
                        ->count(),
                    'description' => 'Authentication attempts that triggered elevated-risk review.',
                ],
            ],
            'deviceOptions' => $isProtectedPage ? $this->deviceOptions($registeredDevices) : [],
            'isProtectedPage' => $isProtectedPage,
            'isPublicPage' => $isPublicPage,
            'showHeroMetrics' => $isProtectedPage,
            'showOperationsPanel' => $isProtectedPage,
        ];
    }

    private function currentAuthenticatedUser(): ?User
    {
        $authenticatedUser = auth()->user();

        if (! $authenticatedUser instanceof User) {
            return null;
        }

        return User::query()
            ->with(['authenticationEvents', 'devices.passkeys', 'passkeys'])
            ->find($authenticatedUser->id);
    }

    /**
     * @param  array<int, array{name: string}>  $registeredDevices
     * @return array<int, string>
     */
    private function deviceOptions(array $registeredDevices): array
    {
        return collect($registeredDevices)
            ->pluck('name')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function redirectWithDemoState(string $route, PasskeyPreviewResult $result): RedirectResponse
    {
        return to_route($route)->with('status', $result->message);
    }
}
