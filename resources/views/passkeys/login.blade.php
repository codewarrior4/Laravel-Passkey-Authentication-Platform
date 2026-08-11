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
        <article class="rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,_rgba(14,20,29,0.96),_rgba(8,10,14,0.98))] p-6 shadow-2xl shadow-black/30 lg:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Trusted sign-in</p>
                    <h3 class="mt-2 text-3xl font-semibold text-white">Use your passkey to continue</h3>
                </div>
                <span class="rounded-full border border-sky-400/30 bg-sky-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-sky-200">Low friction</span>
            </div>

            <form method="POST" action="{{ route('passkeys.login.preview') }}" class="mt-8 grid gap-5">
                @csrf

                <label class="grid gap-2">
                    <span class="text-sm font-medium text-stone-200">Work email</span>
                    <input
                        type="email"
                        name="work_email"
                        value="{{ old('work_email', 'arielle@onely.app') }}"
                        class="rounded-2xl border border-white/10 bg-white/[0.05] px-4 py-3 text-white outline-none ring-0 transition placeholder:text-stone-500 focus:border-sky-300/40"
                        placeholder="name@company.com"
                    >
                    @error('work_email')
                        <span class="text-sm text-rose-300">{{ $message }}</span>
                    @enderror
                </label>

                <label class="grid gap-2">
                    <span class="text-sm font-medium text-stone-200">Preferred device</span>
                    <select
                        name="device_choice"
                        class="rounded-2xl border border-white/10 bg-white/[0.05] px-4 py-3 text-white outline-none ring-0 transition focus:border-sky-300/40"
                    >
                        @foreach ($registeredDevices as $registeredDevice)
                            <option value="{{ $registeredDevice['name'] ?? $registeredDevice['device'] }}">
                                {{ $registeredDevice['name'] ?? $registeredDevice['device'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('device_choice')
                        <span class="text-sm text-rose-300">{{ $message }}</span>
                    @enderror
                </label>

                <div class="rounded-3xl border border-white/10 bg-black/15 p-5">
                    <p class="text-sm font-semibold text-white">How the authentication ceremony should feel</p>
                    <div class="mt-4 grid gap-3 text-sm leading-6 text-stone-300">
                        <p>Today's backend now creates the authentication challenge and records the audit path behind this demo flow.</p>
                        <p>Recovery and risk messaging remain visible so users always know what to do if the expected device is unavailable.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_rgba(125,211,252,1),_rgba(45,212,191,0.95))] px-5 py-3 text-sm font-semibold text-stone-950 shadow-lg shadow-sky-950/40 transition hover:brightness-105">
                        Preview sign-in
                    </button>
                    <a href="{{ route('passkeys.dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.08]">
                        Jump to dashboard view
                    </a>
                </div>
            </form>
        </article>

        <aside class="grid gap-6">
            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Live session expectations</p>
                <div class="mt-5 grid gap-4">
                    @foreach ($recentSessions as $session)
                        <div class="rounded-3xl border border-white/8 bg-black/15 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <h3 class="text-base font-semibold text-white">{{ $session['device'] }}</h3>
                                <span class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-stone-300">{{ $session['status'] }}</span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-stone-300">{{ $session['location'] }}</p>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Recovery guidance</p>
                <p class="mt-4 text-2xl font-semibold text-white">No trusted device nearby? Keep recovery calm, obvious, and safe.</p>
                <p class="mt-4 text-sm leading-7 text-stone-300">This is where fallback paths, support guidance, and suspicious-login reassurance should live once the backend flow is complete.</p>
            </article>
        </aside>
    </section>
</x-passkey-shell>
