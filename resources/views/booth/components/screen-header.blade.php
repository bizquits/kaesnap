<header class="kiosk-screen-header">
    {{-- Kiri: Back --}}
    <div class="flex items-center" style="min-width:80px;">
        @if(!empty($backId))
        <button
            type="button"
            id="{{ $backId }}"
            class="kiosk-btn-back"
            aria-label="Kembali"
            @if(empty($backVisible)) hidden @endif>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="hidden sm:inline text-sm">Kembali</span>
        </button>
        @endif
    </div>

    {{-- Tengah: Judul --}}
    <div class="flex flex-col items-center justify-center text-center flex-1">
        @if(!empty($centerId))
        <span id="{{ $centerId }}" class="text-sm font-semibold tracking-wide" style="color:var(--text);">
            {{ $centerLabel ?? '' }}
        </span>
        @else
        <h1 class="text-base font-semibold tracking-tight" style="color:var(--text);">
            {{ $title ?? '' }}
        </h1>
        @if(!empty($subtitle))
        <p class="text-xs mt-0.5" style="color:var(--text-muted);">{{ $subtitle }}</p>
        @endif
        @endif
    </div>

    {{-- Kanan: Primary action --}}
    <div class="flex justify-end" style="min-width:80px;">
        @if(!empty($primaryId))
        <button
            type="button"
            id="{{ $primaryId }}"
            @if(!empty($primaryDisabled)) disabled @endif
            class="kiosk-btn-primary">
            {{ $primaryLabel ?? 'Lanjut' }}
        </button>
        @endif
    </div>
</header>