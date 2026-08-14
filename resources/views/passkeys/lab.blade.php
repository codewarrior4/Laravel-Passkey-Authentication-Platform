<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Passkey Lab</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-10 px-6 py-10 lg:px-10">
            <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.24),_transparent_28%),linear-gradient(135deg,_rgba(28,25,23,0.96),_rgba(12,10,9,0.96))] p-8 shadow-2xl shadow-amber-950/30 lg:p-12">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-300/80">QA Surface</p>
                        <h1 class="max-w-2xl text-4xl font-semibold tracking-tight text-white lg:text-5xl">Passkey lab for real browser registration, login, and account checks.</h1>
                        <p class="max-w-2xl text-base leading-7 text-stone-300 lg:text-lg">
                            Use this page as the internal test console whenever you want a fast route into registration, sign-in, or the protected dashboard.
                        </p>
                    </div>
                    <div class="grid gap-3 rounded-3xl border border-white/10 bg-white/5 p-5 text-sm text-stone-200 backdrop-blur">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Relying Party</p>
                            <p class="mt-1 text-lg font-semibold text-white">{{ $relyingParty->name }}</p>
                        </div>
                        <div class="grid gap-1 text-stone-300">
                            <p><span class="text-stone-500">ID</span> {{ $relyingParty->id }}</p>
                            <p><span class="text-stone-500">Origins</span> {{ implode(', ', $relyingParty->origins) ?: 'None configured yet' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <article class="rounded-[2rem] border border-white/10 bg-stone-900/80 p-6 shadow-xl shadow-black/20">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-stone-400">Execution Map</p>
                            <h2 class="mt-2 text-2xl font-semibold text-white">Live passkey flow</h2>
                        </div>
                        <span class="rounded-full border border-amber-400/30 bg-amber-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-amber-200">Ready to test</span>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <a href="{{ route('passkeys.register') }}" class="rounded-3xl border border-white/8 bg-white/[0.03] p-5 transition hover:border-amber-300/30 hover:bg-white/[0.05]">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Step 1</p>
                            <h3 class="mt-3 text-lg font-semibold text-white">Registration</h3>
                            <p class="mt-2 text-sm leading-6 text-stone-300">Create a passkey, store the credential, and confirm the browser ceremony completes cleanly.</p>
                        </a>
                        <a href="{{ route('passkeys.login') }}" class="rounded-3xl border border-white/8 bg-white/[0.03] p-5 transition hover:border-sky-300/30 hover:bg-white/[0.05]">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Step 2</p>
                            <h3 class="mt-3 text-lg font-semibold text-white">Authentication</h3>
                            <p class="mt-2 text-sm leading-6 text-stone-300">Run a passkey-first login and verify the session opens without leaking account details beforehand.</p>
                        </a>
                        <a href="{{ route('passkeys.dashboard') }}" class="rounded-3xl border border-white/8 bg-white/[0.03] p-5 transition hover:border-teal-300/30 hover:bg-white/[0.05]">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Step 3</p>
                            <h3 class="mt-3 text-lg font-semibold text-white">Dashboard</h3>
                            <p class="mt-2 text-sm leading-6 text-stone-300">Review devices, passkeys, active sessions, and the audit trail from a protected account view.</p>
                        </a>
                    </div>
                </article>

                <aside class="rounded-[2rem] border border-white/10 bg-stone-900/80 p-6 shadow-xl shadow-black/20">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-stone-400">Feature Flags</p>
                    <div class="mt-5 grid gap-3">
                        @foreach ($featureFlags as $feature)
                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                <span class="text-sm font-medium text-white">{{ $feature['label'] }}</span>
                                <span class="{{ $feature['active'] ? 'border-emerald-400/20 bg-emerald-300/10 text-emerald-100' : 'border-rose-400/20 bg-rose-300/10 text-rose-100' }} rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]">
                                    {{ $feature['active'] ? 'Active' : 'Paused' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </aside>
            </section>

            <section class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-6 shadow-xl shadow-black/20">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-3xl border border-white/8 bg-white/[0.03] p-5">
                        <p class="text-sm font-semibold text-white">Registration checks</p>
                        <p class="mt-3 text-sm leading-6 text-stone-300">Verify browser support, secure context, relying-party domain, and final credential activation.</p>
                    </div>
                    <div class="rounded-3xl border border-white/8 bg-white/[0.03] p-5">
                        <p class="text-sm font-semibold text-white">Login checks</p>
                        <p class="mt-3 text-sm leading-6 text-stone-300">Verify passkey-first login, challenge validation, session creation, and risk-aware audit logging.</p>
                    </div>
                    <div class="rounded-3xl border border-white/8 bg-white/[0.03] p-5">
                        <p class="text-sm font-semibold text-white">Dashboard checks</p>
                        <p class="mt-3 text-sm leading-6 text-stone-300">Verify device rename/revoke, passkey revoke, session ending, and protected-page access control.</p>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
