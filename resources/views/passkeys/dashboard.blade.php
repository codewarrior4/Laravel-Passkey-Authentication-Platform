<x-passkey-shell
    :current-route="$current_route"
    :feature-flags="$featureFlags"
    :hero-metrics="$heroMetrics"
    :navigation-items="$navigationItems"
    :page-copy="$page_copy"
    :page-eyebrow="$page_eyebrow"
    :page-heading="$page_heading"
    :page-title="$page_title"
    :relying-party="$relyingParty"
    :show-hero-metrics="$showHeroMetrics"
    :show-operations-panel="$showOperationsPanel"
>
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="grid gap-6">
            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Passkeys</p>
                        <h3 class="mt-2 text-2xl font-semibold text-white">Remove broken or outdated credentials.</h3>
                    </div>
                    <a href="{{ route('passkeys.register') }}" class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.05] px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/[0.08]">
                        Register a new passkey
                    </a>
                </div>

                <div class="mt-6 grid gap-4">
                    @forelse ($registeredPasskeys as $passkey)
                        @php($statusTone = match ($passkey['status_tone']) {
                            'good' => 'bg-emerald-300/10 text-emerald-100 border-emerald-400/20',
                            default => 'bg-white/[0.04] text-stone-200 border-white/8',
                        })
                        <article class="rounded-3xl border border-white/8 bg-black/15 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h4 class="text-lg font-semibold text-white">{{ $passkey['label'] }}</h4>
                                        <span class="{{ $statusTone }} rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em]">{{ $passkey['status'] }}</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-stone-300">{{ $passkey['device'] }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.16em] text-stone-500">Last used {{ $passkey['last_used'] }}</p>
                                </div>

                                @if ($passkey['status'] !== 'Revoked')
                                    <form method="POST" action="{{ route('passkeys.revoke', $passkey['id']) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-2xl border border-rose-400/30 bg-rose-300/10 px-4 py-2 text-sm font-semibold text-rose-100 transition hover:bg-rose-300/15"
                                        >
                                            Remove passkey
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <article class="rounded-3xl border border-dashed border-white/10 bg-black/15 p-5 text-sm leading-6 text-stone-300">
                            No passkeys are registered for this account yet.
                        </article>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Registered devices</p>
                        <h3 class="mt-2 text-2xl font-semibold text-white">Control every credential anchor.</h3>
                    </div>
                    <a href="{{ route('passkeys.register') }}" class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.05] px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/[0.08]">
                        Add another passkey
                    </a>
                </div>

                <div class="mt-6 grid gap-4">
                    @forelse ($registeredDevices as $device)
                        @php($trustTone = match ($device['trust_tone']) {
                            'good' => 'bg-emerald-300/10 text-emerald-100 border-emerald-400/20',
                            'neutral' => 'bg-sky-300/10 text-sky-100 border-sky-400/20',
                            default => 'bg-white/[0.04] text-stone-200 border-white/8',
                        })
                        <article class="rounded-3xl border border-white/8 bg-black/15 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h4 class="text-lg font-semibold text-white">{{ $device['name'] }}</h4>
                                        <span class="{{ $trustTone }} rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em]">{{ $device['trust'] }}</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-stone-300">{{ $device['type'] }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.16em] text-stone-500">Added {{ $device['created_at'] }}</p>
                                </div>
                                <div class="grid gap-3 text-sm text-stone-300 lg:justify-items-end">
                                    <p class="font-medium text-white">Last used {{ $device['last_used'] }}</p>
                                    <p class="mt-1">{{ $device['passkeys_count'] }} linked passkey {{ \Illuminate\Support\Str::plural('record', $device['passkeys_count']) }}.</p>
                                    @if ($device['trust'] !== 'Revoked')
                                        <form method="POST" action="{{ route('passkeys.devices.rename', $device['id']) }}" class="flex flex-col gap-2 sm:flex-row">
                                            @csrf
                                            <input
                                                type="text"
                                                name="label"
                                                value="{{ $device['name'] }}"
                                                class="rounded-2xl border border-white/10 bg-white/[0.05] px-3 py-2 text-sm text-white outline-none transition focus:border-sky-300/40"
                                            >
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/[0.08]"
                                            >
                                                Rename
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('passkeys.devices.revoke', $device['id']) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center rounded-2xl border border-rose-400/30 bg-rose-300/10 px-4 py-2 text-sm font-semibold text-rose-100 transition hover:bg-rose-300/15"
                                            >
                                                Revoke device
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <article class="rounded-3xl border border-dashed border-white/10 bg-black/15 p-5 text-sm leading-6 text-stone-300">
                            No devices have been registered yet. Complete the registration flow to populate this control center.
                        </article>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Audit timeline</p>
                <div class="mt-6 grid gap-4">
                    @forelse ($recentAuditEvents as $event)
                        <article class="rounded-3xl border border-white/8 bg-black/15 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-base font-semibold text-white">{{ $event['title'] }}</h4>
                                    <p class="mt-2 text-sm leading-6 text-stone-300">{{ $event['detail'] }}</p>
                                </div>
                                <span class="shrink-0 text-xs font-semibold uppercase tracking-[0.18em] text-stone-400">{{ $event['time'] }}</span>
                            </div>
                        </article>
                    @empty
                        <article class="rounded-3xl border border-dashed border-white/10 bg-black/15 p-4 text-sm leading-6 text-stone-300">
                            No audit history has been captured yet.
                        </article>
                    @endforelse
                </div>
            </article>
        </div>

        <aside class="grid gap-6">
            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Active sessions</p>
                <div class="mt-6 grid gap-4">
                    @forelse ($recentSessions as $session)
                        <article class="rounded-3xl border border-white/8 bg-black/15 p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-white">{{ $session['browser'] }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-stone-300">{{ $session['platform'] }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.16em] text-stone-500">{{ $session['ip_address'] }} • Last seen {{ $session['last_seen'] }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-stone-300">{{ $session['status'] }}</span>

                                    @unless ($session['is_current'])
                                        <form method="POST" action="{{ route('passkeys.sessions.revoke', $session['id']) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center rounded-2xl border border-rose-400/30 bg-rose-300/10 px-4 py-2 text-sm font-semibold text-rose-100 transition hover:bg-rose-300/15"
                                            >
                                                End session
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </div>
                        </article>
                    @empty
                        <article class="rounded-3xl border border-dashed border-white/10 bg-black/15 p-4 text-sm leading-6 text-stone-300">
                            No active browser sessions have been stored yet.
                        </article>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Security posture</p>
                <div class="mt-5 grid gap-4">
                    @foreach ($securitySignals as $signal)
                        <div class="rounded-3xl border border-white/8 bg-black/15 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <h3 class="text-base font-semibold text-white">{{ $signal['label'] }}</h3>
                                <span class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-stone-300">{{ $signal['state'] }}</span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-stone-300">{{ $signal['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </article>
        </aside>
    </section>
</x-passkey-shell>
