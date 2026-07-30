@php
    $fallbackExpiresAt = session('spikia.demo_expires_at.' . ($sesion?->slug ?? ''));
    $demoExpiresAt = $sesion?->demo_expires_at ?? ($fallbackExpiresAt ? \Illuminate\Support\Carbon::parse($fallbackExpiresAt) : null);
    $isExpired = (bool) ($demoExpiresAt && now()->greaterThanOrEqualTo($demoExpiresAt));
    $remaining = $demoExpiresAt ? max(0, now()->diffInMinutes($demoExpiresAt, false)) : null;
@endphp

@if($demoExpiresAt)
    <div class="mb-6 rounded-[1.75rem] border px-5 py-4 text-sm font-medium {{ $isExpired ? 'border-red-500/30 bg-red-500/10 text-red-100' : 'border-amber-400/20 bg-amber-400/10 text-amber-100' }}"
        data-demo-banner
        data-demo-expires-at="{{ $demoExpiresAt?->toIso8601String() }}">
        @if($isExpired)
            Se acabo el tiempo del demo, por favor activa otra sesion.
        @else
            Demo activa. Tiempo restante: <span data-demo-countdown>{{ $remaining }} minutos</span>.
        @endif
    </div>
    <script>
        (() => {
            const banner = document.querySelector('[data-demo-banner][data-demo-expires-at="{{ $demoExpiresAt?->toIso8601String() }}"]');
            const label = banner?.querySelector('[data-demo-countdown]');
            const expiresAt = Date.parse(banner?.dataset.demoExpiresAt || '');
            if (!banner || !label || Number.isNaN(expiresAt)) return;

            let timer = null;
            const tick = () => {
                const remaining = Math.max(0, expiresAt - Date.now());
                const minutes = Math.floor(remaining / 60000);
                const seconds = Math.floor((remaining % 60000) / 1000);
                label.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                if (remaining <= 0) {
                    banner.className = 'mb-6 rounded-[1.75rem] border px-5 py-4 text-sm font-medium border-red-500/30 bg-red-500/10 text-red-100';
                    banner.textContent = 'Se acabo el tiempo del demo, por favor activa otra sesion.';
                    if (timer) clearInterval(timer);
                }
            };

            tick();
            timer = setInterval(tick, 1000);
        })();
    </script>
@endif
