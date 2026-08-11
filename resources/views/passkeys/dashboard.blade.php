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
>
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="grid gap-6">
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
                    @foreach ($registeredDevices as $device)
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
                                </div>
                                <div class="text-sm text-stone-300">
                                    <p class="font-medium text-white">Last used {{ $device['last_used'] }}</p>
                                    <p class="mt-1">Revocation and rename actions land here next.</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </article>

            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Audit timeline</p>
                <div class="mt-6 grid gap-4">
                    @foreach ($recentAuditEvents as $event)
                        <article class="rounded-3xl border border-white/8 bg-black/15 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-base font-semibold text-white">{{ $event['title'] }}</h4>
                                    <p class="mt-2 text-sm leading-6 text-stone-300">{{ $event['detail'] }}</p>
                                </div>
                                <span class="shrink-0 text-xs font-semibold uppercase tracking-[0.18em] text-stone-400">{{ $event['time'] }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </article>
        </div>

        <aside class="grid gap-6">
            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Active sessions</p>
                <div class="mt-6 grid gap-4">
                    @foreach ($recentSessions as $session)
                        <article class="rounded-3xl border border-white/8 bg-black/15 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-semibold text-white">{{ $session['device'] }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-stone-300">{{ $session['location'] }}</p>
                                </div>
                                <span class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-stone-300">{{ $session['status'] }}</span>
                            </div>
                        </article>
                    @endforeach
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
