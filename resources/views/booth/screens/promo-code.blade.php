<div class="kiosk-screen flex h-full w-full flex-col items-center justify-center p-6">
    <div class="w-full max-w-sm flex flex-col gap-5 animate-fade-up">

        <div>
            <h1 class="text-2xl font-bold tracking-tight" style="color:var(--text);">Kode Promo</h1>
            <p class="mt-1 text-sm" style="color:var(--text-muted);">Masukkan kode voucher yang Anda miliki</p>
        </div>

        <div class="flex flex-col gap-2">
            <label for="promo-code-input" class="sr-only">Kode promo</label>
            <input type="text" id="promo-code-input"
                placeholder="Contoh: PROMO2024"
                autocomplete="off"
                class="kiosk-input text-base tracking-widest"
                style="letter-spacing:.12em; font-weight:600;" />
            <p id="promo-code-error"
                class="hidden pl-1 text-xs"
                style="color:var(--danger);"></p>
        </div>

        <div class="flex gap-3">
            <button type="button" id="btn-promo-cancel"
                class="flex-1 py-3 text-sm font-medium transition-opacity hover:opacity-70"
                style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-sm); color:var(--text-muted);">
                Batal
            </button>
            <button type="button" id="btn-promo-apply"
                class="kiosk-btn-primary flex-1 py-3.5 text-sm"
                style="border-radius:var(--radius-sm);"
                data-default-label="Terapkan"
                data-loading-label="Memproses...">
                Terapkan
            </button>
        </div>

    </div>
</div>