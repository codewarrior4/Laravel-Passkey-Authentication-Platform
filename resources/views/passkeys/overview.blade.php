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
    <section class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Experience map</p>
                    <h3 class="mt-2 text-2xl font-semibold text-white">Walk the audience through the entire trust journey.</h3>
                </div>
                <span class="rounded-full border border-emerald-400/30 bg-emerald-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-200">Live flow</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <a href="{{ route('passkeys.register') }}" class="group rounded-3xl border border-white/8 bg-black/15 p-5 transition hover:-translate-y-0.5 hover:border-amber-300/30 hover:bg-black/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Step 1</p>
                    <h4 class="mt-3 text-xl font-semibold text-white">Register</h4>
                    <p class="mt-2 text-sm leading-6 text-stone-300">Start with a focused passkey setup experience.</p>
                    <p class="mt-4 text-sm font-semibold text-amber-200 transition group-hover:text-amber-100">Open registration</p>
                </a>

                <a href="{{ route('passkeys.login') }}" class="group rounded-3xl border border-white/8 bg-black/15 p-5 transition hover:-translate-y-0.5 hover:border-sky-300/30 hover:bg-black/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Step 2</p>
                    <h4 class="mt-3 text-xl font-semibold text-white">Login</h4>
                    <p class="mt-2 text-sm leading-6 text-stone-300">Move into a clean sign-in flow with no sensitive hints exposed.</p>
                    <p class="mt-4 text-sm font-semibold text-sky-200 transition group-hover:text-sky-100">Open sign-in</p>
                </a>

                <a href="{{ route('passkeys.dashboard') }}" class="group rounded-3xl border border-white/8 bg-black/15 p-5 transition hover:-translate-y-0.5 hover:border-teal-300/30 hover:bg-black/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Step 3</p>
                    <h4 class="mt-3 text-xl font-semibold text-white">Dashboard</h4>
                    <p class="mt-2 text-sm leading-6 text-stone-300">Arrive in the protected account area after authentication succeeds.</p>
                    <p class="mt-4 text-sm font-semibold text-teal-200 transition group-hover:text-teal-100">Open dashboard</p>
                </a>
            </div>
        </article>

        <aside class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">What this project shows</p>
            <div class="mt-6 grid gap-4 text-sm leading-6 text-stone-300">
                <div class="rounded-3xl border border-white/8 bg-black/15 p-4">A guided passkey registration journey.</div>
                <div class="rounded-3xl border border-white/8 bg-black/15 p-4">A calm sign-in screen that avoids leaking account details.</div>
                <div class="rounded-3xl border border-white/8 bg-black/15 p-4">A protected dashboard reserved for authenticated users.</div>
            </div>
        </aside>
    </section>
</x-passkey-shell>
