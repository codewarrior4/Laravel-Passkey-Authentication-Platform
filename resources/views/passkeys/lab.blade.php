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
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-300/80">Monday Architecture</p>
                        <h1 class="max-w-2xl text-4xl font-semibold tracking-tight text-white lg:text-5xl">Passkey lab shell for the week's real browser testing.</h1>
                        <p class="max-w-2xl text-base leading-7 text-stone-300 lg:text-lg">
                            This page is the permanent UI home for exercising passkey registration, authentication, and device management as each backend flow lands.
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
                            <h2 class="mt-2 text-2xl font-semibold text-white">Week flow ownership</h2>
                        </div>
                        <span class="rounded-full border border-amber-400/30 bg-amber-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-amber-200">UI lives here</span>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-3xl border border-white/8 bg-white/[0.03] p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Tuesday</p>
                            <h3 class="mt-3 text-lg font-semibold text-white">Registration</h3>
                            <p class="mt-2 text-sm leading-6 text-stone-300">Challenge creation, credential storage, and the first end-to-end browser exercise.</p>
                        </div>
                        <div class="rounded-3xl border border-white/8 bg-white/[0.03] p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Wednesday</p>
                            <h3 class="mt-3 text-lg font-semibold text-white">Authentication</h3>
                            <p class="mt-2 text-sm leading-6 text-stone-300">Assertion verification, session creation, and login flow testing in the browser.</p>
                        </div>
                        <div class="rounded-3xl border border-white/8 bg-white/[0.03] p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Thursday</p>
                            <h3 class="mt-3 text-lg font-semibold text-white">Device Control</h3>
                            <p class="mt-2 text-sm leading-6 text-stone-300">Registered device visibility, renaming, revocation, and session safety checks.</p>
                        </div>
                    </div>
                </article>

                <aside class="rounded-[2rem] border border-white/10 bg-stone-900/80 p-6 shadow-xl shadow-black/20">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-stone-400">Feature Flags</p>
                    <div class="mt-5 grid gap-3">
                        @foreach ($featureFlags as $label => $flag)
                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                <span class="text-sm font-medium capitalize text-white">{{ str_replace('_', ' ', $label) }}</span>
                                <code class="text-xs text-amber-200">{{ $flag }}</code>
                            </div>
                        @endforeach
                    </div>
                </aside>
            </section>

            <section class="rounded-[2rem] border border-dashed border-white/15 bg-stone-900/70 p-6 shadow-xl shadow-black/20">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-stone-400">Readiness</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white">The UI shell is ready before the first auth endpoint lands.</h2>
                        <p class="mt-3 text-sm leading-6 text-stone-300">
                            Monday stops at architecture. The forms and buttons below are intentionally non-destructive placeholders so the browser test surface has a stable home from day one.
                        </p>
                    </div>

                    <div class="grid w-full max-w-xl gap-4">
                        <div class="rounded-3xl border border-white/8 bg-white/[0.03] p-5">
                            <div class="flex items-center justify-between gap-4">
                                <h3 class="text-lg font-semibold text-white">Registration console</h3>
                                <span class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">Pending</span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-stone-300">Reserved for the Tuesday WebAuthn registration ceremony.</p>
                            <button type="button" disabled class="mt-4 inline-flex w-full items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-stone-500">
                                Registration flow unlocks Tuesday
                            </button>
                        </div>

                        <div class="rounded-3xl border border-white/8 bg-white/[0.03] p-5">
                            <div class="flex items-center justify-between gap-4">
                                <h3 class="text-lg font-semibold text-white">Authentication console</h3>
                                <span class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">Pending</span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-stone-300">Reserved for the Wednesday assertion verification and session creation flow.</p>
                            <button type="button" disabled class="mt-4 inline-flex w-full items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-stone-500">
                                Authentication flow unlocks Wednesday
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
