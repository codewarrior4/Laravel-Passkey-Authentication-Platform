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
        <article class="rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,_rgba(14,20,29,0.96),_rgba(8,10,14,0.98))] p-6 shadow-2xl shadow-black/30 lg:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Trusted sign-in</p>
                    <h3 class="mt-2 text-3xl font-semibold text-white">Use your passkey to continue</h3>
                </div>
                <span class="rounded-full border border-sky-400/30 bg-sky-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-sky-200">Secure entry</span>
            </div>

            <form
                method="POST"
                action="{{ route('passkeys.login.start') }}"
                class="mt-8 grid gap-5"
                data-passkey-login
                data-finish-url="{{ route('passkeys.login.finish') }}"
            >
                @csrf

                <div class="rounded-3xl border border-white/10 bg-black/15 p-5">
                    <p class="text-sm font-semibold text-white">What to expect</p>
                    <div class="mt-4 grid gap-3 text-sm leading-6 text-stone-300">
                        <p>Your browser looks for a saved passkey on this device.</p>
                        <p>Approve with your fingerprint, Face ID, or device unlock.</p>
                        <p>Only after that succeeds do you enter the protected dashboard.</p>
                    </div>
                </div>

                <div class="hidden rounded-3xl border border-rose-400/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-100" data-passkey-error></div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_rgba(125,211,252,1),_rgba(45,212,191,0.95))] px-5 py-3 text-sm font-semibold text-stone-950 shadow-lg shadow-sky-950/40 transition hover:brightness-105"
                        data-passkey-submit
                    >
                        Sign in with passkey
                    </button>
                    <a href="{{ route('passkeys.register') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.08]">
                        Create a passkey first
                    </a>
                </div>
            </form>
        </article>

        <aside class="grid gap-6">
            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Protected access</p>
                <p class="mt-4 text-2xl font-semibold text-white">The dashboard only opens after a successful session is created.</p>
                <p class="mt-4 text-sm leading-7 text-stone-300">This page stays intentionally clean. It does not reveal registered accounts, trusted devices, or active sessions before authentication succeeds.</p>
            </article>

            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Simple flow</p>
                <div class="mt-5 grid gap-4 text-sm leading-6 text-stone-300">
                    <div class="rounded-3xl border border-white/8 bg-black/15 p-4">
                        Enter your work email.
                    </div>
                    <div class="rounded-3xl border border-white/8 bg-black/15 p-4">
                        Approve the request with your passkey.
                    </div>
                    <div class="rounded-3xl border border-white/8 bg-black/15 p-4">
                        Continue into the protected dashboard.
                    </div>
                </div>
            </article>
        </aside>
    </section>
</x-passkey-shell>
