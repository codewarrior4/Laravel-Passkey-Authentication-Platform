@props([
    'currentRoute',
    'featureFlags' => [],
    'heroMetrics' => [],
    'navigationItems' => [],
    'pageCopy',
    'pageEyebrow',
    'pageHeading',
    'pageTitle',
    'relyingParty',
    'showHeroMetrics' => false,
    'showOperationsPanel' => false,
])

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $pageTitle }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--color-ink)] text-stone-100 antialiased">
        <div class="relative isolate overflow-hidden">
            <div class="absolute inset-x-0 top-0 -z-10 h-[32rem] bg-[radial-gradient(circle_at_top_left,_rgba(248,113,113,0.24),_transparent_30%),radial-gradient(circle_at_top_right,_rgba(45,212,191,0.16),_transparent_22%),linear-gradient(180deg,_rgba(17,24,39,0.96),_rgba(9,9,11,1))]"></div>
            <div class="absolute left-1/2 top-20 -z-10 h-80 w-80 -translate-x-1/2 rounded-full bg-amber-400/8 blur-3xl"></div>

            <main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-8 px-5 py-6 lg:px-8">
                <header class="rounded-[2rem] border border-white/10 bg-white/[0.04] px-5 py-4 shadow-xl shadow-black/20 backdrop-blur lg:px-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_rgba(251,191,36,0.95),_rgba(239,68,68,0.9))] text-sm font-semibold text-stone-950 shadow-lg shadow-amber-950/30">
                                PK
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-400">Onely Authentication Platform</p>
                                <h1 class="mt-1 text-lg font-semibold text-white">Passkey Experience Suite</h1>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <nav class="flex flex-wrap items-center gap-2">
                            @foreach ($navigationItems as $navigationItem)
                                @php($isActive = $currentRoute === $navigationItem['route'])
                                <a
                                    href="{{ route($navigationItem['route']) }}"
                                    class="{{ $isActive ? 'bg-white text-stone-950 shadow-lg shadow-white/10' : 'bg-white/[0.04] text-stone-300 hover:bg-white/[0.08] hover:text-white' }} inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition"
                                >
                                    {{ $navigationItem['label'] }}
                                </a>
                            @endforeach
                            </nav>

                            @auth
                                <form method="POST" action="{{ route('passkeys.logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.04] px-4 py-2 text-sm font-semibold text-stone-300 transition hover:bg-white/[0.08] hover:text-white"
                                    >
                                        Sign out
                                    </button>
                                </form>
                            @endauth
                        </div>
                    </div>
                </header>

                <section class="grid gap-6 {{ $showOperationsPanel ? 'lg:grid-cols-[1.2fr_0.8fr]' : '' }}">
                    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.2),_transparent_28%),linear-gradient(135deg,_rgba(28,25,23,0.92),_rgba(12,10,9,0.95))] p-7 shadow-2xl shadow-black/30 lg:p-9">
                        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300/80">{{ $pageEyebrow }}</p>
                        <h2 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-white lg:text-5xl">{{ $pageHeading }}</h2>
                        <p class="mt-5 max-w-3xl text-base leading-8 text-stone-300 lg:text-lg">{{ $pageCopy }}</p>

                        @if ($showHeroMetrics && count($heroMetrics) > 0)
                            <div class="mt-8 grid gap-4 md:grid-cols-3">
                                @foreach ($heroMetrics as $heroMetric)
                                    <article class="rounded-3xl border border-white/10 bg-white/[0.05] p-5">
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-400">{{ $heroMetric['label'] }}</p>
                                        <p class="mt-3 text-3xl font-semibold text-white">{{ $heroMetric['value'] }}</p>
                                        <p class="mt-2 text-sm leading-6 text-stone-300">{{ $heroMetric['detail'] }}</p>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($showOperationsPanel)
                        <aside class="grid gap-5 rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl shadow-black/20 backdrop-blur">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Relying Party</p>
                                <p class="mt-2 text-2xl font-semibold text-white">{{ $relyingParty->name }}</p>
                                <div class="mt-4 grid gap-2 text-sm text-stone-300">
                                    <p><span class="text-stone-500">Identifier</span> {{ $relyingParty->id }}</p>
                                    <p><span class="text-stone-500">Origins</span> {{ count($relyingParty->origins) }} trusted origin {{ \Illuminate\Support\Str::plural('entry', count($relyingParty->origins)) }}</p>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Release controls</p>
                                <div class="mt-4 grid gap-3">
                                    @foreach ($featureFlags as $feature)
                                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/8 bg-black/15 px-4 py-3">
                                            <span class="text-sm font-medium text-white">{{ $feature['label'] }}</span>
                                            <span class="{{ $feature['active'] ? 'border-emerald-400/20 bg-emerald-300/10 text-emerald-100' : 'border-rose-400/20 bg-rose-300/10 text-rose-100' }} rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]">
                                                {{ $feature['active'] ? 'Active' : 'Paused' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </aside>
                    @endif
                </section>

                @if (session('status'))
                    <div class="rounded-[1.75rem] border border-emerald-400/20 bg-emerald-300/10 px-5 py-4 text-sm leading-7 text-emerald-100 shadow-lg shadow-emerald-950/20">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
