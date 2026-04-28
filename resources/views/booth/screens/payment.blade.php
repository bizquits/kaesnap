<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
    'backId' => 'btn-payment-back',
    'backVisible' => true,
    'title' => 'Pembayaran',
    'subtitle' => 'Pilih metode pembayaran',
    ])

    <div class="kiosk-screen-body flex flex-1 flex-col items-center justify-center p-6 overflow-y-auto gap-6">

        {{-- Pilih jumlah print --}}
        <div id="payment-copy-section" class="w-full max-w-xl">
            <p class="mb-2.5 text-xs font-semibold uppercase tracking-widest"
                style="color:var(--text-muted); letter-spacing:.08em;">Jumlah Cetak</p>
            <div id="payment-copy-options" class="grid grid-cols-3 gap-2.5"></div>
            <p id="payment-selected-price" class="mt-2 text-sm hidden" style="color:var(--text-muted);"></p>
        </div>

        {{-- Metode pembayaran --}}
        <div id="payment-cards" class="mx-auto grid w-full max-w-xl grid-cols-1 gap-3 sm:grid-cols-2">

            {{-- QRIS --}}
            <button type="button" id="btn-payment-qris"
                class="payment-card flex flex-col items-center gap-3 p-7 text-center transition-all">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl"
                    style="background:rgba(74,222,128,0.1);">
                    <svg class="h-8 w-8" style="color:#4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </span>
                <div>
                    <p class="font-semibold" style="color:var(--text);">QRIS</p>
                    <p class="mt-0.5 text-xs" style="color:var(--text-muted);">Bayar via Midtrans</p>
                </div>
            </button>

            {{-- Voucher --}}
            <div class="payment-card flex flex-col gap-3 p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                        style="background:rgba(251,191,36,0.1);">
                        <svg class="h-6 w-6" style="color:#fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-sm" style="color:var(--text);">Voucher</p>
                        <p class="text-xs" style="color:var(--text-muted);">Kode dari dashboard</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <input type="text" id="payment-voucher-input"
                        placeholder="Kode voucher" autocomplete="off"
                        class="kiosk-input flex-1 text-sm py-2.5" />
                    <button type="button" id="btn-payment-voucher-apply"
                        class="kiosk-btn-primary shrink-0 text-sm px-4"
                        style="border-radius:var(--radius-sm);">
                        Pakai
                    </button>
                </div>
                <p id="payment-voucher-error" class="hidden text-xs" style="color:var(--danger);"></p>
            </div>
        </div>

        {{-- Gratis --}}
        <div id="payment-free-wrap" class="hidden">
            <button type="button" id="btn-payment-free"
                class="kiosk-btn-primary px-10 py-4 text-base"
                style="border-radius:var(--radius);">
                Lanjut (Gratis)
            </button>
        </div>

    </div>
</div>