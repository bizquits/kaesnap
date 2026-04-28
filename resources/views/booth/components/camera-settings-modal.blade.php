<div id="camera-settings-modal"
    class="booth-modal-backdrop hidden"
    aria-hidden="true" role="dialog"
    aria-labelledby="camera-settings-title"
    aria-modal="true">

    <div class="booth-modal" style="width:420px;">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-5">
            <h2 id="camera-settings-title"
                class="text-base font-semibold"
                style="color:var(--text);">
                Pengaturan Perangkat
            </h2>
            <button type="button" id="camera-settings-close"
                class="booth-icon-btn" aria-label="Tutup">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Kamera --}}
        <div class="mb-4 flex flex-col gap-2">
            <label for="camera-settings-select"
                class="text-xs font-semibold uppercase tracking-widest"
                style="color:var(--text-muted); letter-spacing:.08em;">
                Kamera
            </label>
            <select id="camera-settings-select" class="kiosk-input text-sm">
                <option value="">Memuat kamera...</option>
            </select>
        </div>

        {{-- Preview --}}
        <div class="mb-5">
            <label class="mb-2 block text-xs font-semibold uppercase tracking-widest"
                style="color:var(--text-muted); letter-spacing:.08em;">
                Preview
            </label>
            <div class="relative overflow-hidden" style="aspect-ratio:4/3; border-radius:var(--radius-sm); background:var(--bg-raised);">
                <video id="camera-settings-preview"
                    class="h-full w-full object-cover -scale-x-100"
                    autoplay playsinline muted></video>
                <div id="camera-settings-placeholder"
                    class="absolute inset-0 flex items-center justify-center"
                    style="color:var(--text-dim);">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Printer --}}
        <div class="flex flex-col gap-3 mb-5 pt-4"
            style="border-top:1px solid var(--border);">
            <p class="text-xs font-semibold uppercase tracking-widest"
                style="color:var(--text-muted); letter-spacing:.08em;">Printer</p>

            <div>
                <label for="camera-settings-printer-type"
                    class="mb-1.5 block text-xs"
                    style="color:var(--text-muted);">Tipe Koneksi</label>
                <select id="camera-settings-printer-type" class="kiosk-input text-sm"></select>
            </div>

            <div class="flex items-center justify-between rounded-xl px-4 py-3"
                style="background:var(--bg-raised);">
                <span class="text-sm" style="color:var(--text-muted);">Status</span>
                <span id="camera-settings-printer-status"
                    class="text-sm font-semibold"
                    style="color:var(--danger);">Tidak Terhubung</span>
            </div>

            <button type="button" id="camera-settings-connect-printer"
                class="kiosk-btn-primary w-full py-3 text-sm"
                style="border-radius:var(--radius-sm);">
                Hubungkan Printer
            </button>
        </div>

        {{-- Simpan --}}
        <div style="border-top:1px solid var(--border); padding-top:1rem;">
            <button type="button" id="camera-settings-save"
                class="kiosk-btn-primary w-full py-3 text-sm"
                style="border-radius:var(--radius-sm);">
                Simpan
            </button>
        </div>

    </div>
</div>