<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
    'backId' => 'btn-review-back',
    'backVisible' => true,
    'title' => 'ORDER REVIEW',
    'subtitle' => 'Confirm your prints',
    ])

    <div class="kiosk-screen-body flex flex-1 flex-col items-center justify-center p-6 overflow-y-auto">
        <div class="w-full max-w-sm flex flex-col gap-3 animate-fade-up">

            {{-- Order card --}}
            <div class="arcade-panel" style="padding:1.25rem;">
                {{-- Copies selector --}}
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <p style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:0.8rem;
                                   letter-spacing:0.12em;text-transform:uppercase;color:var(--text);">
                            COPIES
                        </p>
                        <p style="font-family:'Rajdhani',sans-serif;font-size:0.7rem;
                                   color:var(--text-dim);margin-top:0.15rem;letter-spacing:0.06em;">
                            Select quantity
                        </p>
                        <span id="review-copy-promo" class="hidden"
                            style="font-family:'Rajdhani',sans-serif;font-size:0.7rem;color:var(--green);
                                   letter-spacing:0.06em;"></span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0"
                        style="border:1px solid var(--border-md);border-radius:var(--radius-sm);
                               background:var(--bg-raised);">
                        <button type="button" id="review-copy-minus"
                            class="flex h-10 w-10 items-center justify-center transition-colors hover:opacity-70"
                            style="color:var(--text-muted);" aria-label="Decrease">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </button>
                        <span id="review-copy-value"
                            style="min-width:2rem;text-align:center;font-family:'Orbitron',monospace;
                                   font-weight:700;font-size:1.1rem;color:var(--cyan);
                                   text-shadow:0 0 8px var(--cyan-glow);">1</span>
                        <button type="button" id="review-copy-plus"
                            class="flex h-10 w-10 items-center justify-center transition-colors hover:opacity-70"
                            style="color:var(--text-muted);" aria-label="Increase">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="height:1px;background:var(--border);margin-bottom:1rem;"></div>

                {{-- Price breakdown --}}
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between"
                        style="font-family:'Rajdhani',sans-serif;font-size:0.8rem;font-weight:600;
                               letter-spacing:0.06em;">
                        <span style="color:var(--text-muted);">SUBTOTAL</span>
                        <span id="review-subtotal" style="color:var(--text);">Rp 0</span>
                    </div>

                    <div id="review-discount-row" class="hidden flex justify-between"
                        style="font-family:'Rajdhani',sans-serif;font-size:0.8rem;font-weight:600;
                               letter-spacing:0.06em;">
                        <span style="color:var(--green);display:flex;align-items:center;gap:0.5rem;">
                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 9a3 3 0 1 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 1 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z" />
                                <path d="M9 9h.01m6 6-6-6m0 6h.01" />
                            </svg>
                            <span id="review-discount-label">VOUCHER</span>
                        </span>
                        <span id="review-discount" style="color:var(--green);">-Rp 0</span>
                    </div>

                    <div style="height:1px;background:var(--border);margin:0.25rem 0;"></div>

                    <div class="flex justify-between"
                        style="font-family:'Rajdhani',sans-serif;font-size:0.9rem;font-weight:700;
                               letter-spacing:0.08em;">
                        <span style="color:var(--text);">TOTAL</span>
                        <span id="review-total"
                            style="color:var(--cyan);text-shadow:0 0 8px var(--cyan-glow);">Rp 0</span>
                    </div>
                </div>
            </div>

            {{-- Error --}}
            <div id="review-payment-error" class="hidden px-4 py-3"
                style="background:rgba(255,62,108,0.08);border:1px solid rgba(255,62,108,0.25);
                       border-radius:var(--radius-sm);color:var(--danger);
                       font-family:'Rajdhani',sans-serif;font-weight:700;
                       font-size:0.8rem;letter-spacing:0.08em;text-transform:uppercase;"
                role="alert"></div>

            {{-- Promo toggle --}}
            <button type="button" id="btn-review-promo"
                class="w-full py-3"
                style="background:var(--bg-card);border:1px solid var(--border);
                       border-radius:var(--radius-sm);color:var(--text-muted);
                       font-family:'Rajdhani',sans-serif;font-weight:700;font-size:0.75rem;
                       letter-spacing:0.1em;text-transform:uppercase;cursor:pointer;
                       transition:all 0.15s ease;">
                ENTER PROMO CODE
            </button>

            {{-- Promo section --}}
            <div id="review-promo-section" class="hidden flex-col gap-2">
                <div class="flex gap-2">
                    <input type="text" id="review-promo-input"
                        placeholder="PROMO CODE"
                        autocomplete="off"
                        class="kiosk-input flex-1"
                        style="font-family:'Rajdhani',sans-serif;font-weight:700;
                               letter-spacing:0.1em;text-transform:uppercase;font-size:0.85rem;
                               padding-top:0.625rem;padding-bottom:0.625rem;" />
                    <button type="button" id="btn-review-promo-apply"
                        class="kiosk-btn-primary shrink-0 px-4"
                        style="font-size:0.75rem;"
                        data-default-label="APPLY"
                        data-loading-label="...">
                        APPLY
                    </button>
                </div>
                <p id="review-promo-error" class="hidden text-xs pl-1"
                    style="color:var(--danger);font-family:'Rajdhani',sans-serif;
                           font-weight:700;letter-spacing:0.06em;"></p>
                <div id="review-promo-success" class="hidden items-center gap-1.5 text-xs pl-1"
                    style="color:var(--green);font-family:'Rajdhani',sans-serif;
                           font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span id="review-promo-success-msg">VOUCHER APPLIED!</span>
                    <button type="button" id="btn-review-promo-remove"
                        class="ml-auto underline underline-offset-2"
                        style="color:var(--text-muted);font-size:0.7rem;
                               font-family:'Rajdhani',sans-serif;font-weight:700;cursor:pointer;">
                        REMOVE
                    </button>
                </div>
            </div>

            {{-- Proceed button --}}
            <button type="button" id="btn-review-to-payment"
                class="kiosk-btn-primary w-full"
                style="padding:1rem;font-size:0.85rem;"
                data-default-label="PROCEED TO PAYMENT"
                data-loading-label="PROCESSING...">
                PROCEED TO PAYMENT
            </button>
        </div>
    </div>
</div>