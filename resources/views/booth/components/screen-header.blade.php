<header class="kiosk-screen-header">
    {{-- Kiri: Back --}}
    <div class="flex items-center" style="min-width:90px;">
        @if(!empty($backId))
        <button
            type="button"
            id="{{ $backId }}"
            class="kiosk-btn-back"
            aria-label="Kembali"
            @if(empty($backVisible)) hidden @endif>
            {{-- Pixel back arrow --}}
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="hidden sm:inline" style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase;">BACK</span>
        </button>
        @endif
    </div>

    {{-- Tengah: HUD Title --}}
    <div class="flex flex-col items-center justify-center text-center flex-1">
        @if(!empty($centerId))
        <span id="{{ $centerId }}"
            style="font-family:'Orbitron',monospace;font-weight:700;font-size:0.8rem;letter-spacing:0.12em;color:var(--cyan);text-shadow:0 0 10px var(--cyan-glow);">
            {{ $centerLabel ?? '' }}
        </span>
        @else
        {{-- Corner pixel decorators --}}
        <div style="position:relative;display:inline-flex;align-items:center;gap:0.75rem;">
            <span style="color:var(--blue);font-size:0.6rem;opacity:0.7;">◀</span>
            <div>
                <h1 style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:1rem;letter-spacing:0.18em;text-transform:uppercase;color:var(--text);text-shadow:0 0 12px var(--blue-glow);margin:0;">
                    {{ $title ?? '' }}
                </h1>
                @if(!empty($subtitle))
                <p style="font-family:'Rajdhani',sans-serif;font-size:0.65rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-dim);margin:0.1rem 0 0;">
                    {{ $subtitle }}
                </p>
                @endif
            </div>
            <span style="color:var(--blue);font-size:0.6rem;opacity:0.7;">▶</span>
        </div>
        @endif
    </div>

    {{-- Kanan: Primary action --}}
    <div class="flex justify-end" style="min-width:90px;">
        @if(!empty($primaryId))
        <button
            type="button"
            id="{{ $primaryId }}"
            @if(!empty($primaryDisabled)) disabled @endif
            class="kiosk-btn-primary">
            {{ $primaryLabel ?? 'NEXT' }}
        </button>
        @endif
    </div>
</header>