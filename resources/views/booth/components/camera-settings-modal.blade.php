<div id="camera-settings-modal"
    class="booth-modal-backdrop hidden"
    aria-hidden="true" role="dialog"
    aria-labelledby="camera-settings-title"
    aria-modal="true">

    <div class="booth-modal" style="width:420px;">

        {{-- ══════════════════════════════════════════
             PANEL 1: PIN / Password Gate
             Ditampilkan pertama saat modal dibuka.
        ═══════════════════════════════════════════ --}}
        <div id="camera-settings-pin-panel">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-base font-semibold" style="color:var(--text);">
                        Pengaturan Perangkat
                    </h2>
                    <p class="text-xs mt-0.5" style="color:var(--text-muted);">
                        Masukkan PIN untuk melanjutkan
                    </p>
                </div>
                <button type="button" id="camera-settings-pin-close"
                    class="booth-icon-btn" aria-label="Tutup">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Lock icon --}}
            <div class="flex justify-center mb-5">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl"
                    style="background:var(--bg-raised); border:1px solid var(--border);">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        style="color:var(--text-muted);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            {{-- PIN Dots Display --}}
            <div id="camera-settings-pin-dots"
                class="flex justify-center gap-3 mb-5"
                aria-label="PIN input" aria-live="polite">
                @for ($i = 0; $i < 4; $i++)
                    <div class="pin-dot h-4 w-4 rounded-full transition-all duration-150"
                    style="background:var(--border);"
                    data-index="{{ $i }}">
            </div>
            @endfor
        </div>

        {{-- Error message --}}
        <p id="camera-settings-pin-error"
            class="hidden text-center text-sm mb-4"
            style="color:var(--danger);">
            PIN salah. Coba lagi.
        </p>

        {{-- Numpad --}}
        <div class="grid grid-cols-3 gap-2.5 mb-3">
            @foreach([1,2,3,4,5,6,7,8,9] as $n)
            <button type="button"
                class="pin-numpad-btn h-14 rounded-xl text-xl font-semibold transition-all active:scale-95"
                style="background:var(--bg-raised); border:1px solid var(--border); color:var(--text);"
                data-digit="{{ $n }}">
                {{ $n }}
            </button>
            @endforeach

            {{-- Row bawah: kosong | 0 | hapus --}}
            <div></div>
            <button type="button"
                class="pin-numpad-btn h-14 rounded-xl text-xl font-semibold transition-all active:scale-95"
                style="background:var(--bg-raised); border:1px solid var(--border); color:var(--text);"
                data-digit="0">
                0
            </button>
            <button type="button"
                id="pin-backspace"
                class="h-14 rounded-xl flex items-center justify-center transition-all active:scale-95"
                style="background:var(--bg-raised); border:1px solid var(--border); color:var(--text-muted);"
                aria-label="Hapus">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                </svg>
            </button>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
             PANEL 2: Isi Camera Settings
             Ditampilkan setelah PIN benar.
        ═══════════════════════════════════════════ --}}
    <div id="camera-settings-content-panel" class="hidden">

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
</div>