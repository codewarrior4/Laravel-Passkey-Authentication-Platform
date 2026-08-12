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
    <section class="mx-auto w-full max-w-3xl">
        <article class="rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,_rgba(20,23,30,0.96),_rgba(8,10,14,0.98))] p-6 shadow-2xl shadow-black/30 lg:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Passkey setup</p>
                    <h3 class="mt-2 text-3xl font-semibold text-white">Create a passkey</h3>
                </div>
                <span class="rounded-full border border-amber-400/30 bg-amber-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-amber-200">Secure setup</span>
            </div>

            <p class="mt-5 max-w-2xl text-sm leading-7 text-stone-300">
                Create a passkey with a clean public-facing flow. Private account details stay hidden until sign-in succeeds.
            </p>

            <form
                method="POST"
                action="{{ route('passkeys.register.start') }}"
                class="mt-8 grid gap-5"
                data-passkey-register
                data-finish-url="{{ route('passkeys.register.finish') }}"
            >
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-stone-200">Full name</span>
                        <input
                            type="text"
                            name="full_name"
                            value="{{ old('full_name') }}"
                            class="rounded-2xl border border-white/10 bg-white/[0.05] px-4 py-3 text-white outline-none ring-0 transition placeholder:text-stone-500 focus:border-amber-300/40"
                            placeholder="Full name"
                        >
                        @error('full_name')
                            <span class="text-sm text-rose-300">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-stone-200">Work email</span>
                        <input
                            type="email"
                            name="work_email"
                            value="{{ old('work_email') }}"
                            class="rounded-2xl border border-white/10 bg-white/[0.05] px-4 py-3 text-white outline-none ring-0 transition placeholder:text-stone-500 focus:border-amber-300/40"
                            placeholder="name@company.com"
                        >
                        @error('work_email')
                            <span class="text-sm text-rose-300">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                <label class="grid gap-2">
                    <span class="text-sm font-medium text-stone-200">Name this device</span>
                    <input
                        type="text"
                        name="device_name"
                        value="{{ old('device_name') }}"
                        class="rounded-2xl border border-white/10 bg-white/[0.05] px-4 py-3 text-white outline-none ring-0 transition placeholder:text-stone-500 focus:border-amber-300/40"
                        placeholder="Name this device"
                    >
                    @error('device_name')
                        <span class="text-sm text-rose-300">{{ $message }}</span>
                    @enderror
                </label>

                <div class="grid gap-3 rounded-3xl border border-white/10 bg-black/15 p-5">
                    <p class="text-sm font-semibold text-white">Simple flow</p>
                    <div class="grid gap-3 text-sm leading-6 text-stone-300">
                        <p>1. Enter your details.</p>
                        <p>2. Approve the passkey request on your device.</p>
                        <p>3. Continue to sign-in after setup completes.</p>
                    </div>
                </div>

                <div class="hidden rounded-3xl border border-rose-400/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-100" data-passkey-error></div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_rgba(251,191,36,1),_rgba(239,68,68,0.92))] px-5 py-3 text-sm font-semibold text-stone-950 shadow-lg shadow-amber-950/40 transition hover:brightness-105"
                        data-passkey-submit
                    >
                        Register passkey in browser
                    </button>
                    <a href="{{ route('passkeys.login') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.08]">
                        Continue to sign-in
                    </a>
                </div>
            </form>
        </article>
    </section>
</x-passkey-shell>
