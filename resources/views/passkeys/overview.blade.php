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
    <section class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Experience map</p>
                    <h3 class="mt-2 text-2xl font-semibold text-white">Walk the audience through the entire trust journey.</h3>
                </div>
                <span class="rounded-full border border-emerald-400/30 bg-emerald-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-200">Demo ready</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <a href="{{ route('passkeys.register') }}" class="group rounded-3xl border border-white/8 bg-black/15 p-5 transition hover:-translate-y-0.5 hover:border-amber-300/30 hover:bg-black/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Step 1</p>
                    <h4 class="mt-3 text-xl font-semibold text-white">Register</h4>
                    <p class="mt-2 text-sm leading-6 text-stone-300">Introduce the passkey enrollment experience with device naming, trust cues, and rollout messaging.</p>
                    <p class="mt-4 text-sm font-semibold text-amber-200 transition group-hover:text-amber-100">Open registration</p>
                </a>

                <a href="{{ route('passkeys.login') }}" class="group rounded-3xl border border-white/8 bg-black/15 p-5 transition hover:-translate-y-0.5 hover:border-sky-300/30 hover:bg-black/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Step 2</p>
                    <h4 class="mt-3 text-xl font-semibold text-white">Login</h4>
                    <p class="mt-2 text-sm leading-6 text-stone-300">Show a calm authentication flow with trusted device hints, recovery language, and clear next steps.</p>
                    <p class="mt-4 text-sm font-semibold text-sky-200 transition group-hover:text-sky-100">Open sign-in</p>
                </a>

                <a href="{{ route('passkeys.dashboard') }}" class="group rounded-3xl border border-white/8 bg-black/15 p-5 transition hover:-translate-y-0.5 hover:border-teal-300/30 hover:bg-black/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Step 3</p>
                    <h4 class="mt-3 text-xl font-semibold text-white">Dashboard</h4>
                    <p class="mt-2 text-sm leading-6 text-stone-300">Land in a security center that tells the story of devices, sessions, audit signals, and operational control.</p>
                    <p class="mt-4 text-sm font-semibold text-teal-200 transition group-hover:text-teal-100">Open dashboard</p>
                </a>
            </div>
        </article>

        <aside class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Recent audit story</p>
            <div class="mt-6 grid gap-4">
                @foreach ($recentAuditEvents as $event)
                    @php($toneClass = match ($event['tone']) {
                        'alert' => 'border-rose-400/20 bg-rose-300/10 text-rose-100',
                        'good' => 'border-emerald-400/20 bg-emerald-300/10 text-emerald-100',
                        default => 'border-white/8 bg-black/15 text-stone-200',
                    })
                    <article class="{{ $toneClass }} rounded-3xl border p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4 class="text-base font-semibold">{{ $event['title'] }}</h4>
                                <p class="mt-2 text-sm leading-6">{{ $event['detail'] }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold uppercase tracking-[0.18em] text-stone-400">{{ $event['time'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </aside>
    </section>
</x-passkey-shell>
