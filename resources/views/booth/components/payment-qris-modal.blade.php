<div
    id="payment-qris-modal"
    class="booth-modal-backdrop hidden"
    aria-hidden="true"
    role="dialog"
    aria-modal="true">
    <div class="booth-modal" style="width:460px; max-width:95vw;">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-semibold" style="color:var(--text);">
                    Bayar via QRIS
                </h2>
                <p class="text-xs mt-0.5" style="color:var(--text-muted);">
                    Scan QR menggunakan aplikasi dompet digital
                </p>
            </div>
            <button
                type="button"
                id="btn-payment-modal-close"
                class="booth-icon-btn"
                aria-label="Tutup">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Amount --}}
        <div class="text-center mb-5">
            <p class="text-xs uppercase tracking-widest mb-1" style="color:var(--text-muted);">
                Total Pembayaran
            </p>
            <p id="payment-modal-amount"
                class="text-3xl font-bold"
                style="color:var(--text);">
                Rp 0
            </p>
        </div>

        {{-- QR Code --}}
        <div class="flex justify-center mb-5">
            <div
                id="payment-modal-qr-wrap"
                class="flex items-center justify-center rounded-2xl"
                style="width:240px; height:240px; background:#fff; border:3px solid rgba(255,255,255,0.15);">
                {{-- Loading --}}
                <div id="payment-modal-qr-loading" class="flex flex-col items-center gap-3">
                    <svg class="h-8 w-8 animate-spin" style="color:var(--text-dim);" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <p class="text-xs" style="color:var(--text-dim);">Memuat QR...</p>
                </div>
                {{-- Image --}}
                <img
                    id="payment-modal-qr-img"
                    src=""
                    alt="QRIS"
                    class="hidden w-full h-full object-contain rounded-xl" />
            </div>
        </div>

        {{-- Status --}}
        <div class="flex items-center justify-center gap-2 py-3 px-4 rounded-2xl mb-5"
            style="background:rgba(255,255,255,0.06); border:1px solid var(--border);">
            <span id="payment-modal-status-dot"
                class="h-2 w-2 rounded-full animate-pulse"
                style="background:var(--text-muted);"></span>
            <p id="payment-modal-status"
                class="text-sm font-medium"
                style="color:var(--text-muted);">
                Menunggu pembayaran...
            </p>
        </div>

        {{-- Sandbox helper (hanya di sandbox) --}}
        <div id="payment-modal-sandbox" class="hidden mb-4 rounded-xl p-4"
            style="background:rgba(251,191,36,0.1); border:1px solid rgba(251,191,36,0.25);">
            <p class="text-xs font-semibold mb-2" style="color:#fbbf24;">Mode Sandbox — Simulator</p>
            <p class="text-xs mb-2" style="color:var(--text-muted);">
                Buka
                <a href="https://simulator.sandbox.midtrans.com/v2/qris/index"
                    target="_blank" rel="noopener"
                    class="underline" style="color:#fbbf24;">
                    Simulator QRIS Midtrans
                </a>,
                paste URL QR di bawah, lalu klik <strong>Pay</strong>.
            </p>
            <div class="flex gap-2 items-center">
                <input
                    id="payment-modal-sandbox-url"
                    type="text"
                    readonly
                    class="flex-1 rounded-lg px-2 py-1.5 text-xs font-mono truncate"
                    style="background:rgba(0,0,0,0.3); color:var(--text); border:1px solid var(--border);" />
                <button
                    type="button"
                    id="btn-payment-modal-copy"
                    class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-semibold"
                    style="background:#fbbf24; color:#000;">
                    Copy
                </button>
            </div>
        </div>

        {{-- Cancel --}}
        <button
            type="button"
            id="btn-payment-modal-cancel"
            class="w-full py-3 text-sm font-medium"
            style="background:var(--bg-raised); border:1px solid var(--border);
                   border-radius:var(--radius-sm); color:var(--text-muted);">
            Batalkan Pembayaran
        </button>

    </div>
</div>