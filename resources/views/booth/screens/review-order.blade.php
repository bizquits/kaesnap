<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
    'backId' => 'btn-review-back',
    'backVisible' => true,
    'title' => 'Tinjau Pesanan',
    ])

    <div class="kiosk-screen-body flex flex-1 flex-col items-center justify-center p-6 overflow-y-auto">
        <div class="w-full max-w-sm flex flex-col gap-3 animate-fade-up">

            {{-- Card pesanan --}}
            <div class="rounded-2xl p-5 flex flex-col gap-4"
                style="background:var(--bg-card); border:1px solid var(--border);">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-medium text-sm" style="color:var(--text);">Jumlah Cetak</p>
                        <p class="text-xs mt-0.5" style="color:var(--text-muted);">Pilih berapa eksemplar</p>
                        <span id="review-copy-promo"
                            class="hidden mt-1 text-xs"
                            style="color:var(--text-muted);"></span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0"
                        style="border:1px solid var(--border); border-radius:var(--radius-sm);">
                        <button type="button" id="review-copy-minus"
                            class="flex h-9 w-9 items-center justify-center transition-colors hover:opacity-70"
                            style="color:var(--text-muted);" aria-label="Kurangi">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </button>
                        <span id="review-copy-value"
                            class="min-w-8 text-center text-sm font-semibold"
                            style="color:var(--text);">1</span>
                        <button type="button" id="review-copy-plus"
                            class="flex h-9 w-9 items-center justify-center transition-colors hover:opacity-70"
                            style="color:var(--text-muted);" aria-label="Tambah">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="height:1px; background:var(--border);"></div>

                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between text-sm">
                        <span style="color:var(--text-muted);">Subtotal</span>
                        <span id="review-subtotal" style="color:var(--text);">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm font-semibold">
                        <span style="color:var(--text);">Total</span>
                        <span id="review-total" style="color:var(--text);">Rp 0</span>
                    </div>
                </div>
            </div>

            <div id="review-payment-error"
                class="hidden rounded-xl px-4 py-3 text-sm"
                style="background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2); color:var(--danger);"
                role="alert"></div>

            <button type="button" id="btn-review-promo"
                class="w-full py-3 text-sm font-medium transition-opacity hover:opacity-70"
                style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-sm); color:var(--text-muted);">
                Punya kode promo?
            </button>

            <button type="button" id="btn-review-to-payment"
                class="kiosk-btn-primary w-full py-4 text-sm"
                style="border-radius:var(--radius-sm);"
                data-default-label="Lanjutkan ke Pembayaran"
                data-loading-label="Memproses...">
                Lanjutkan ke Pembayaran
            </button>
        </div>
    </div>
</div>