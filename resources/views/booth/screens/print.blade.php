<div class="kiosk-screen flex h-full w-full flex-col items-center justify-center p-6 gap-6">

    <div id="print-status" class="flex flex-col items-center gap-5 animate-fade-up">
        <div class="relative flex h-20 w-20 items-center justify-center rounded-full"
            style="background:var(--bg-card); border:1px solid var(--border);">
            <svg class="h-8 w-8 animate-spin" style="color:var(--text-muted);" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
        </div>
        <div class="text-center">
            <p class="font-semibold" style="color:var(--text);">Mencetak...</p>
            <p class="mt-1 text-sm" style="color:var(--text-muted);">Harap tunggu sebentar</p>
        </div>
    </div>

    <div id="print-done" class="hidden flex-col items-center gap-5 animate-fade-up text-center">
        <div class="flex h-20 w-20 items-center justify-center rounded-full"
            style="background:rgba(74,222,128,0.1); border:1px solid rgba(74,222,128,0.2);">
            <svg class="h-8 w-8" style="color:var(--success);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div>
            <p class="font-semibold" style="color:var(--text);">Selesai!</p>
            <p class="mt-1 text-sm" style="color:var(--text-muted);">Foto Anda telah dicetak</p>
        </div>
        <button type="button" id="btn-print-next"
            class="kiosk-btn-primary px-10 py-3.5"
            style="border-radius:var(--radius);">
            Lanjut
        </button>
    </div>

</div>