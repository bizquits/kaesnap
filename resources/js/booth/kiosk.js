/**
 * Kiosk main entry.
 * Wires state machine, frames, camera, session.
 * No auto fullscreen, no auto session start – homescreen has Start, Lock, Camera Settings.
 */

import { createStateMachine } from "./state.js";
import { initFrames } from "./frames.js";
import { createCamera } from "./camera.js";
import { createSession } from "./session.js";
import { mergePhotoWithFrame } from "./image-processing.js";
import { initCameraSettings } from "./camera-settings.js";
import { initWelcomeScreen } from "./welcome.js";
import { printPhotostrip, isPrinterConnected } from "./printer.js";

document.addEventListener("DOMContentLoaded", () => {
    // Initialize welcome screen
    initWelcomeScreen();

    const session = createSession();
    const framesData = JSON.parse(document.body.dataset.frames || "[]");
    const setting = JSON.parse(document.body.dataset.setting || "{}");
    const resultUrl = document.body.dataset.resultUrl || "";

    let camera = null;
    let selectedPhotoIndex = 0;
    let mergedImageDataUrl = null;
    let selectedFrameData = null;

    let isUploaded = false;

    // Result screen auto-redirect timer
    let _resultTimerInterval = null;
    const RESULT_TIMEOUT_SECONDS = 60;

    // ── Promo/voucher state (inline on review-order screen) ──
    let appliedVoucherCode = null;
    let appliedDiscountAmount = 0;
    let appliedVoucherType = null; // 'fixed' | 'percent'
    let appliedVoucherValue = 0; // nilai mentah dari server
    let promoApplyInProgress = false;

    // price_per_session: array JSON { "1": 10000, "2": 15000, ... }
    const pricePerSlot = (() => {
        try {
            const raw = document.body.dataset.pricePerSession || "{}";
            const obj = JSON.parse(raw);
            const result = {};
            for (const [k, v] of Object.entries(obj)) {
                const n = parseInt(k, 10);
                if (n >= 1) result[n] = parseInt(v, 10);
            }
            return Object.keys(result).length ? result : { 1: 0 };
        } catch {
            return { 1: 0 };
        }
    })();

    // copy_prices: integer — harga flat per eksemplar tambahan
    const copyPricePerUnit = parseInt(
        document.body.dataset.copyPriceOptions || "0",
        10,
    );

    let selectedCopyCount = 1;
    const createPaymentUrl = document.body.dataset.createPaymentUrl || "";
    const validateVoucherUrl = document.body.dataset.validateVoucherUrl || "";
    const applyVoucherUrl = document.body.dataset.applyVoucherUrl || "";
    const confirmFreeUrl = document.body.dataset.confirmFreeUrl || "";
    const csrfToken = document.body.dataset.csrf || "";

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────

    function getSelectedFrameId() {
        const frameCard = document.querySelector(".frame-card.border-blue-600");
        if (frameCard) return parseInt(frameCard.dataset.frameId, 10);
        const fromBody = document.body.dataset.selectedFrameId;
        return fromBody ? parseInt(fromBody, 10) : null;
    }

    function formatRp(n) {
        return "Rp " + (n || 0).toLocaleString("id-ID");
    }

    /** Hitung subtotal berdasarkan frame yang dipilih + jumlah cetak */
    function calcSubtotal() {
        const frame = framesData.find((f) => f.id === getSelectedFrameId());
        const slotCount = frame?.photo_slots?.length ?? 1;
        const basePrice = pricePerSlot[slotCount] ?? pricePerSlot[1] ?? 0;
        return (
            basePrice + copyPricePerUnit * Math.max(0, selectedCopyCount - 1)
        );
    }

    // ─────────────────────────────────────────────────────────
    // PROMO UI HELPERS
    // ─────────────────────────────────────────────────────────

    function _resetPromoUI() {
        const section = document.getElementById("review-promo-section");
        const input = document.getElementById("review-promo-input");
        const errEl = document.getElementById("review-promo-error");
        const successEl = document.getElementById("review-promo-success");
        const btn = document.getElementById("btn-review-promo");
        const discountRow = document.getElementById("review-discount-row");

        if (section) {
            section.classList.add("hidden");
            section.classList.remove("flex");
        }
        if (input) {
            input.value = "";
            input.disabled = false;
        }
        if (errEl) {
            errEl.textContent = "";
            errEl.classList.add("hidden");
        }
        if (successEl) {
            successEl.classList.add("hidden");
            successEl.classList.remove("flex");
        }
        // Tampilkan kembali tombol promo
        if (btn) {
            btn.textContent = "Punya kode promo?";
            btn.classList.remove("hidden");
        }
        if (discountRow) {
            discountRow.classList.add("hidden");
            discountRow.classList.remove("flex");
        }

        // Reset state voucher
        appliedVoucherType = null;
        appliedVoucherValue = 0;

        // Reset apply button
        const applyBtn = document.getElementById("btn-review-promo-apply");
        if (applyBtn) {
            applyBtn.disabled = false;
            applyBtn.classList.remove("hidden");
            applyBtn.textContent = applyBtn.dataset.defaultLabel ?? "Pakai";
        }
    }

    /**
     * Hitung ulang diskon secara lokal — tanpa API call, instan.
     * Dipanggil saat copy count berubah dan voucher sedang aktif.
     */
    function recalcDiscount() {
        if (!appliedVoucherCode || !appliedVoucherType) return;
        const subtotal = calcSubtotal();
        if (appliedVoucherType === "percent") {
            appliedDiscountAmount = Math.round(
                (subtotal * Math.min(100, Math.max(0, appliedVoucherValue))) /
                    100,
            );
        } else {
            // fixed: diskon tidak melebihi subtotal
            appliedDiscountAmount = Math.min(subtotal, appliedVoucherValue);
        }
        // Perbarui pesan sukses
        const successMsg = document.getElementById("review-promo-success-msg");
        if (successMsg) {
            successMsg.textContent =
                appliedDiscountAmount > 0
                    ? `Voucher "${appliedVoucherCode}" diterapkan! Hemat ${formatRp(appliedDiscountAmount)}`
                    : `Voucher "${appliedVoucherCode}" diterapkan (gratis)!`;
        }
    }

    function _showPromoSuccess(code, discountAmt) {
        const errEl = document.getElementById("review-promo-error");
        const successEl = document.getElementById("review-promo-success");
        const successMsg = document.getElementById("review-promo-success-msg");
        const input = document.getElementById("review-promo-input");

        if (errEl) {
            errEl.textContent = "";
            errEl.classList.add("hidden");
        }
        if (input) input.disabled = true;
        if (successEl) {
            successEl.classList.remove("hidden");
            successEl.classList.add("flex");
        }
        if (successMsg) {
            successMsg.textContent =
                discountAmt > 0
                    ? `Voucher "${code}" diterapkan! Hemat ${formatRp(discountAmt)}`
                    : `Voucher "${code}" diterapkan (gratis)!`;
        }

        const applyBtn = document.getElementById("btn-review-promo-apply");
        if (applyBtn) applyBtn.classList.add("hidden");
    }

    // ─────────────────────────────────────────────────────────
    // REVIEW ORDER DISPLAY
    // ─────────────────────────────────────────────────────────

    function updateReviewOrderDisplay() {
        const copyValueEl = document.getElementById("review-copy-value");
        const subtotalEl = document.getElementById("review-subtotal");
        const totalEl = document.getElementById("review-total");
        const discountRowEl = document.getElementById("review-discount-row");
        const discountEl = document.getElementById("review-discount");
        const discountLabelEl = document.getElementById(
            "review-discount-label",
        );
        const promoHintEl = document.getElementById("review-copy-promo");
        const minusBtn = document.getElementById("review-copy-minus");

        const subtotal = calcSubtotal();
        const discountAmt = appliedDiscountAmount || 0;
        const total = Math.max(0, subtotal - discountAmt);

        if (copyValueEl) copyValueEl.textContent = selectedCopyCount;
        if (subtotalEl) subtotalEl.textContent = formatRp(subtotal);
        if (totalEl) totalEl.textContent = formatRp(total);

        // Discount row
        if (discountRowEl) {
            if (discountAmt > 0 && appliedVoucherCode) {
                discountRowEl.classList.remove("hidden");
                discountRowEl.classList.add("flex");
                if (discountLabelEl)
                    discountLabelEl.textContent = `Voucher (${appliedVoucherCode})`;
                if (discountEl)
                    discountEl.textContent = "-" + formatRp(discountAmt);
            } else {
                discountRowEl.classList.add("hidden");
                discountRowEl.classList.remove("flex");
            }
        }

        if (promoHintEl) {
            promoHintEl.classList.toggle("hidden", selectedCopyCount < 2);
            promoHintEl.textContent =
                selectedCopyCount >= 2
                    ? `Kamu dapat ${selectedCopyCount} strip!`
                    : "";
        }
        if (minusBtn) minusBtn.disabled = selectedCopyCount <= 1;
    }

    function getReviewOrderCopyLimits() {
        const maxCopy = parseInt(
            document.body.dataset.setting
                ? (JSON.parse(document.body.dataset.setting).copies ?? 5)
                : 5,
            10,
        );
        return { min: 1, max: maxCopy };
    }

    function initReviewOrderScreen() {
        const { min: minCopy, max: maxCopy } = getReviewOrderCopyLimits();
        if (selectedCopyCount < minCopy || selectedCopyCount > maxCopy) {
            selectedCopyCount = minCopy;
        }

        // Reset promo state setiap masuk ke screen ini
        appliedVoucherCode = null;
        appliedDiscountAmount = 0;
        _resetPromoUI();

        updateReviewOrderDisplay();
        document
            .getElementById("review-payment-error")
            ?.classList.add("hidden");
        document.getElementById("review-payment-error")?.replaceChildren();

        // Reset tombol lanjutkan
        reviewToPaymentInProgress = false;
        const reviewBtn = document.getElementById("btn-review-to-payment");
        if (reviewBtn) {
            reviewBtn.disabled = false;
            reviewBtn.removeAttribute("aria-busy");
            reviewBtn.textContent =
                reviewBtn.dataset.defaultLabel ?? "Lanjutkan ke Pembayaran";
        }
    }

    // ─────────────────────────────────────────────────────────
    // PAYMENT COPY SELECTOR (legacy screen, mungkin tidak dipakai)
    // ─────────────────────────────────────────────────────────

    function initPaymentCopySelector() {
        const container = document.getElementById("payment-copy-options");
        const priceEl = document.getElementById("payment-selected-price");
        const freeBtn = document.getElementById("btn-payment-free");
        if (!container) return;
        container.innerHTML = "";
        const copyPriceOptions = { [selectedCopyCount]: calcSubtotal() };
        const sorted = Object.keys(copyPriceOptions)
            .map(Number)
            .sort((a, b) => a - b);
        sorted.forEach((n) => {
            const price = copyPriceOptions[n];
            const btn = document.createElement("button");
            btn.type = "button";
            btn.dataset.copies = n;
            btn.dataset.price = price;
            btn.className = `copy-option payment-copy-btn group rounded-2xl border p-4 text-center transition-all hover:border-gray-300 ${selectedCopyCount === n ? "border-gray-900 bg-gray-900 text-white" : "border-gray-200 bg-white"}`;
            btn.innerHTML = `
        <div class="text-sm ${selectedCopyCount === n ? "text-gray-200" : "text-gray-500"}">Print</div>
        <div class="text-2xl font-bold ${selectedCopyCount === n ? "text-white" : "text-gray-900"}">${n}x</div>
        <div class="mt-1 text-sm font-medium ${selectedCopyCount === n ? "text-gray-200" : "text-gray-600"}">${price > 0 ? "+ Rp" + price.toLocaleString("id-ID") : "Gratis"}</div>
      `;
            container.appendChild(btn);
        });
        const firstPrice = copyPriceOptions[selectedCopyCount] ?? 0;
        if (priceEl) {
            priceEl.classList.remove("hidden");
            priceEl.textContent =
                firstPrice > 0
                    ? `Total: Rp ${firstPrice.toLocaleString("id-ID")}`
                    : "Total: Gratis";
        }
        if (freeBtn) {
            freeBtn.textContent =
                firstPrice > 0 ? "Pilih opsi gratis" : "Lanjut (Gratis)";
            freeBtn.disabled = firstPrice > 0;
        }
    }

    // ─────────────────────────────────────────────────────────
    // STATE MACHINE
    // ─────────────────────────────────────────────────────────

    const initialState = document.body.dataset.initialState || "IDLE";
    const stateMachine = createStateMachine(
        {
            IDLE: () => {},
            REVIEW_ORDER: (state, prev) => {
                initReviewOrderScreen();
            },
            PROMO_CODE: (state, prev) => {
                // Kept for backward compat but no longer navigated to from review-order
                const input = document.getElementById("promo-code-input");
                const errEl = document.getElementById("promo-code-error");
                if (errEl) {
                    errEl.classList.add("hidden");
                    errEl.textContent = "";
                }
                if (input) {
                    input.value = "";
                    input.focus();
                }
                const btn = document.getElementById("btn-promo-apply");
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = btn.dataset.defaultLabel ?? "Terapkan";
                }
            },
            PAYMENT: (state, prev) => {
                initPaymentCopySelector();
                const cards = document.getElementById("payment-cards");
                const freeWrap = document.getElementById("payment-free-wrap");
                const voucherError = document.getElementById(
                    "payment-voucher-error",
                );
                const subtotal = calcSubtotal();
                if (cards) cards.style.display = subtotal > 0 ? "" : "none";
                if (freeWrap) freeWrap.classList.toggle("hidden", subtotal > 0);
                if (voucherError) {
                    voucherError.classList.add("hidden");
                    voucherError.textContent = "";
                }
            },
            FRAME: () => {},
            CAPTURE: (state, prev) => {
                if (prev !== "PREVIEW") {
                    const countdownSeconds = setting.countdown_seconds ?? 3;
                    const selectedFrame = framesData.find(
                        (f) => f.id === getSelectedFrameId(),
                    );
                    const maxPhotos = selectedFrame?.photo_slots?.length ?? 1;
                    camera = createCamera(session, {
                        countdownSeconds,
                        maxPhotos,
                    });
                    camera?.reset();
                    applyCaptureSlotOverlay(framesData, getSelectedFrameId());
                }
            },
            PREVIEW: (state, prev) => {
                if (prev === "CAPTURE" && camera) {
                    const photos = camera.getPhotos();
                    selectedPhotoIndex = 0;
                    renderPreviewPhotos(photos);
                    selectedFrameData = framesData.find(
                        (f) => f.id === getSelectedFrameId(),
                    );
                    mergedImageDataUrl = null;

                    if (selectedFrameData && photos.length > 0) {
                        renderPreviewLayout(selectedFrameData.frame_file);
                    } else {
                        const merged =
                            document.getElementById("preview-merged");
                        if (merged)
                            merged.innerHTML =
                                '<p class="text-gray-500 text-sm">Tidak ada foto tersedia</p>';
                    }

                    const printBtn =
                        document.getElementById("btn-preview-print");
                    if (printBtn) {
                        printBtn.disabled = true;
                        printBtn.classList.remove(
                            "bg-blue-600",
                            "text-white",
                            "cursor-pointer",
                        );
                        printBtn.classList.add(
                            "bg-gray-200",
                            "text-gray-500",
                            "cursor-not-allowed",
                        );
                    }
                }
            },
            PRINT: (state, prev) => {
                if (prev === "PREVIEW" && mergedImageDataUrl) {
                    doPrint();
                }
            },
            RESULT: (state, prev) => {
                handleResultPage();
            },
            DONE: () => {},
            RESET: (state, prev) => {
                stopResultTimer();
                camera?.stop();
                camera = null;
                mergedImageDataUrl = null;
                isUploaded = false;
                selectedFrameData = null;
                stateMachine.setState(stateMachine.STATES.IDLE);
            },
        },
        initialState,
    );

    // ─────────────────────────────────────────────────────────
    // CAPTURE SLOT OVERLAY
    // ─────────────────────────────────────────────────────────

    function applyCaptureSlotOverlay(frames, selectedFrameId) {
        const overlay = document.getElementById("capture-slot-overlay");
        const slotTop = document.getElementById("slot-top");
        const slotLeft = document.getElementById("slot-left");
        const slotRight = document.getElementById("slot-right");
        const slotBottom = document.getElementById("slot-bottom");
        if (!overlay || !slotTop || !slotLeft || !slotRight || !slotBottom)
            return;

        const frame = frames.find((f) => f.id === selectedFrameId);
        const slots = frame?.photo_slots;
        const slot = Array.isArray(slots) && slots.length > 0 ? slots[0] : null;

        if (
            !slot ||
            slot.width == null ||
            slot.height == null ||
            slot.width <= 0 ||
            slot.height <= 0
        ) {
            overlay.classList.add("hidden");
            return;
        }

        const cameraAspect = 4 / 3;
        const slotAspect = slot.width / slot.height;
        let leftPct, topPct, widthPct, heightPct;

        if (slotAspect >= cameraAspect) {
            widthPct = 100;
            heightPct = (slot.height / slot.width) * cameraAspect * 100;
            leftPct = 0;
            topPct = (100 - heightPct) / 2;
        } else {
            heightPct = 100;
            widthPct = (slot.width / slot.height / cameraAspect) * 100;
            topPct = 0;
            leftPct = (100 - widthPct) / 2;
        }

        slotTop.style.height = `${topPct}%`;
        slotLeft.style.top = `${topPct}%`;
        slotLeft.style.left = "0";
        slotLeft.style.width = `${leftPct}%`;
        slotLeft.style.height = `${heightPct}%`;
        slotRight.style.top = `${topPct}%`;
        slotRight.style.left = `${leftPct + widthPct}%`;
        slotRight.style.width = `${100 - leftPct - widthPct}%`;
        slotRight.style.height = `${heightPct}%`;
        slotBottom.style.top = `${topPct + heightPct}%`;
        slotBottom.style.height = `${100 - topPct - heightPct}%`;

        overlay.classList.remove("hidden");
    }

    // ─────────────────────────────────────────────────────────
    // FRAME SELECTION
    // ─────────────────────────────────────────────────────────

    const frames = initFrames(stateMachine, session);

    function goToFrame() {
        stateMachine.setState(stateMachine.STATES.FRAME);
    }

    const welcomeScreen = document.getElementById("screen-welcome");
    welcomeScreen?.addEventListener("click", (e) => {
        if (
            e.target.closest(".welcome-start-btn") ||
            e.target.closest(".welcome-component--button")
        ) {
            e.preventDefault();
            goToFrame();
        }
    });
    document.querySelectorAll(".welcome-start-btn").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            goToFrame();
        });
    });
    const btnStartById = document.getElementById("btn-start");
    if (btnStartById && !btnStartById.classList.contains("welcome-start-btn")) {
        btnStartById.addEventListener("click", goToFrame);
    }

    // ─────────────────────────────────────────────────────────
    // REVIEW ORDER — COPY COUNT
    // ─────────────────────────────────────────────────────────

    document
        .getElementById("btn-review-back")
        ?.addEventListener("click", () => {
            stateMachine.setState(stateMachine.STATES.FRAME);
        });

    const { min: reviewMinCopy, max: reviewMaxCopy } =
        getReviewOrderCopyLimits();

    document
        .getElementById("review-copy-minus")
        ?.addEventListener("click", () => {
            if (selectedCopyCount <= reviewMinCopy) return;
            selectedCopyCount--;
            if (appliedVoucherCode) recalcDiscount();
            updateReviewOrderDisplay();
        });

    document
        .getElementById("review-copy-plus")
        ?.addEventListener("click", () => {
            if (selectedCopyCount >= reviewMaxCopy) return;
            selectedCopyCount++;
            if (appliedVoucherCode) recalcDiscount();
            updateReviewOrderDisplay();
        });

    // ─────────────────────────────────────────────────────────
    // REVIEW ORDER — INLINE PROMO
    // ─────────────────────────────────────────────────────────

    // Toggle tampilan section promo
    document
        .getElementById("btn-review-promo")
        ?.addEventListener("click", () => {
            const section = document.getElementById("review-promo-section");
            const btn = document.getElementById("btn-review-promo");
            if (!section) return;

            const isHidden = section.classList.contains("hidden");
            if (isHidden) {
                section.classList.remove("hidden");
                section.classList.add("flex");
                if (btn) btn.textContent = "Tutup";
                document.getElementById("review-promo-input")?.focus();
            } else {
                section.classList.add("hidden");
                section.classList.remove("flex");
                if (btn) btn.textContent = "Punya kode promo?";
            }
        });

    // Enter key di input promo
    document
        .getElementById("review-promo-input")
        ?.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                document.getElementById("btn-review-promo-apply")?.click();
            }
        });

    // Input promo — hapus error saat ketik
    document
        .getElementById("review-promo-input")
        ?.addEventListener("input", () => {
            const errEl = document.getElementById("review-promo-error");
            if (errEl) {
                errEl.classList.add("hidden");
                errEl.textContent = "";
            }
        });

    // Apply voucher inline
    document
        .getElementById("btn-review-promo-apply")
        ?.addEventListener("click", async () => {
            if (promoApplyInProgress) return;

            const input = document.getElementById("review-promo-input");
            const errEl = document.getElementById("review-promo-error");
            const applyBtn = document.getElementById("btn-review-promo-apply");
            const code = input?.value?.trim();

            if (!code) {
                if (errEl) {
                    errEl.textContent = "Masukkan kode promo terlebih dahulu";
                    errEl.classList.remove("hidden");
                }
                return;
            }
            if (!validateVoucherUrl) return;

            promoApplyInProgress = true;
            if (applyBtn) {
                applyBtn.disabled = true;
                applyBtn.textContent = applyBtn.dataset.loadingLabel ?? "...";
            }
            if (errEl) {
                errEl.classList.add("hidden");
                errEl.textContent = "";
            }

            try {
                // Validasi voucher ke backend
                const validateRes = await fetch(validateVoucherUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify({
                        code,
                        copy_count: selectedCopyCount,
                    }),
                });

                const validateData = await validateRes.json().catch(() => ({}));

                if (!validateRes.ok || !validateData.valid) {
                    if (errEl) {
                        errEl.textContent =
                            validateData.message || "Voucher tidak valid";
                        errEl.classList.remove("hidden");
                    }
                    if (applyBtn) {
                        applyBtn.disabled = false;
                        applyBtn.textContent =
                            applyBtn.dataset.defaultLabel ?? "Pakai";
                    }
                    promoApplyInProgress = false;
                    return;
                }

                const discountAmt = validateData.discount_amount ?? 0;
                const amountAfterDiscount =
                    validateData.amount_after_discount ?? 0;

                // Jika 100% diskon → langsung apply voucher dan lanjut ke CAPTURE
                if (amountAfterDiscount <= 0) {
                    const applyRes = await fetch(applyVoucherUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({
                            code,
                            copy_count: selectedCopyCount,
                        }),
                    });
                    const applyData = await applyRes.json().catch(() => ({}));

                    if (applyRes.ok && applyData.success) {
                        stateMachine.setState(stateMachine.STATES.CAPTURE);
                    } else {
                        if (errEl) {
                            errEl.textContent =
                                applyData.message || "Gagal menerapkan voucher";
                            errEl.classList.remove("hidden");
                        }
                        if (applyBtn) {
                            applyBtn.disabled = false;
                            applyBtn.textContent =
                                applyBtn.dataset.defaultLabel ?? "Pakai";
                        }
                    }
                    promoApplyInProgress = false;
                    return;
                }

                // Diskon parsial → simpan state dan perbarui tampilan
                appliedVoucherCode = code;
                appliedDiscountAmount = discountAmt;
                appliedVoucherType = validateData.voucher_type ?? "fixed";
                appliedVoucherValue = validateData.voucher_value ?? discountAmt;

                _showPromoSuccess(code, discountAmt);
                updateReviewOrderDisplay();

                // Tutup section & sembunyikan tombol promo (voucher sudah aktif)
                const section = document.getElementById("review-promo-section");
                const promoToggleBtn =
                    document.getElementById("btn-review-promo");
                if (section) {
                    section.classList.add("hidden");
                    section.classList.remove("flex");
                }
                if (promoToggleBtn) promoToggleBtn.classList.add("hidden");
            } catch (err) {
                console.error("Promo apply error:", err);
                if (errEl) {
                    errEl.textContent = "Gagal memproses. Coba lagi.";
                    errEl.classList.remove("hidden");
                }
                if (applyBtn) {
                    applyBtn.disabled = false;
                    applyBtn.textContent =
                        applyBtn.dataset.defaultLabel ?? "Pakai";
                }
            } finally {
                promoApplyInProgress = false;
            }
        });

    // Hapus voucher yang sudah diterapkan
    document
        .getElementById("btn-review-promo-remove")
        ?.addEventListener("click", () => {
            appliedVoucherCode = null;
            appliedDiscountAmount = 0;
            _resetPromoUI();
            updateReviewOrderDisplay();
        });

    // ─────────────────────────────────────────────────────────
    // REVIEW ORDER — LANJUT KE PEMBAYARAN
    // ─────────────────────────────────────────────────────────

    let reviewToPaymentInProgress = false;

    document
        .getElementById("btn-review-to-payment")
        ?.addEventListener("click", async () => {
            if (reviewToPaymentInProgress) return;
            reviewToPaymentInProgress = true;

            const btn = document.getElementById("btn-review-to-payment");
            const defaultLabel =
                btn?.dataset.defaultLabel ?? "Lanjutkan ke Pembayaran";
            const loadingLabel = btn?.dataset.loadingLabel ?? "Memproses...";
            if (btn) {
                btn.disabled = true;
                btn.setAttribute("aria-busy", "true");
                btn.textContent = loadingLabel;
            }

            function resetButton() {
                reviewToPaymentInProgress = false;
                if (btn) {
                    btn.disabled = false;
                    btn.removeAttribute("aria-busy");
                    btn.textContent = defaultLabel;
                }
            }

            const subtotal = calcSubtotal();
            const discountAmt = appliedDiscountAmount || 0;
            const totalToPay = Math.max(0, subtotal - discountAmt);

            const reviewErrorEl = document.getElementById(
                "review-payment-error",
            );
            function showReviewError(msg) {
                if (reviewErrorEl) {
                    reviewErrorEl.textContent =
                        msg || "Gagal memproses pembayaran. Coba lagi.";
                    reviewErrorEl.classList.remove("hidden");
                }
            }
            function hideReviewError() {
                if (reviewErrorEl) {
                    reviewErrorEl.textContent = "";
                    reviewErrorEl.classList.add("hidden");
                }
            }
            hideReviewError();

            try {
                // Kasus: voucher parsial sudah diterapkan, total > 0 → createPayment dengan voucher_code
                if (appliedVoucherCode && totalToPay > 0) {
                    if (!createPaymentUrl) {
                        resetButton();
                        return;
                    }
                    const res = await fetch(createPaymentUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({
                            copy_count: selectedCopyCount,
                            voucher_code: appliedVoucherCode,
                        }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        showReviewError(data.message || `Error ${res.status}`);
                        resetButton();
                        return;
                    }
                    const redirectUrl = data.redirect_url;
                    if (!redirectUrl) {
                        showReviewError(
                            data.message ||
                                "Tidak ada redirect. Coba mulai sesi baru.",
                        );
                        resetButton();
                        return;
                    }
                    window.location.href = redirectUrl;
                    return;
                }

                // Kasus: gratis (subtotal 0 atau voucher 100% sudah di-apply sebelumnya via button)
                if (totalToPay <= 0) {
                    if (!confirmFreeUrl) {
                        resetButton();
                        return;
                    }
                    const res = await fetch(confirmFreeUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({ copy_count: selectedCopyCount }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success) {
                        stateMachine.setState(stateMachine.STATES.CAPTURE);
                    } else {
                        resetButton();
                    }
                    return;
                }

                // Kasus: berbayar tanpa voucher
                if (!createPaymentUrl) {
                    resetButton();
                    return;
                }
                const res = await fetch(createPaymentUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify({ copy_count: selectedCopyCount }),
                });
                const data = await res.json().catch(() => ({}));
                if (data.snap_token) {
                    showReviewError(
                        "Backend masih mengembalikan Snap. Pastikan deploy terbaru.",
                    );
                    resetButton();
                    return;
                }
                const redirectUrl = data.redirect_url;
                if (!redirectUrl) {
                    showReviewError(
                        data.message ||
                            "Tidak ada redirect. Coba mulai sesi baru.",
                    );
                    resetButton();
                    return;
                }
                if (!res.ok) {
                    showReviewError(data.message || `Error ${res.status}`);
                    resetButton();
                    return;
                }
                window.location.href = redirectUrl;
            } catch (err) {
                console.error("Payment error", err);
                showReviewError(
                    err.message ||
                        "Koneksi gagal. Periksa jaringan dan coba lagi.",
                );
                resetButton();
            }
        });

    // ─────────────────────────────────────────────────────────
    // PROMO CODE SCREEN (legacy — tidak dipakai dari review-order)
    // ─────────────────────────────────────────────────────────

    document
        .getElementById("btn-promo-cancel")
        ?.addEventListener("click", () => {
            stateMachine.setState(stateMachine.STATES.REVIEW_ORDER);
        });

    // ─────────────────────────────────────────────────────────
    // PAYMENT SCREEN
    // ─────────────────────────────────────────────────────────

    document
        .getElementById("btn-payment-back")
        ?.addEventListener("click", () => {
            stateMachine.setState(stateMachine.STATES.REVIEW_ORDER);
        });

    document
        .getElementById("btn-payment-free")
        ?.addEventListener("click", async () => {
            if (!confirmFreeUrl) return;
            try {
                const res = await fetch(confirmFreeUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify({ copy_count: selectedCopyCount }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    stateMachine.setState(stateMachine.STATES.CAPTURE);
                } else {
                    console.error(
                        "Confirm free failed",
                        data.message || res.statusText,
                    );
                }
            } catch (err) {
                console.error("Confirm free error", err);
            }
        });

    document
        .getElementById("btn-payment-voucher-apply")
        ?.addEventListener("click", async () => {
            const input = document.getElementById("payment-voucher-input");
            const errEl = document.getElementById("payment-voucher-error");
            const code = input?.value?.trim();
            if (!code) {
                if (errEl) {
                    errEl.textContent = "Masukkan kode voucher";
                    errEl.classList.remove("hidden");
                }
                return;
            }
            if (!applyVoucherUrl) return;
            try {
                const res = await fetch(applyVoucherUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify({
                        code,
                        copy_count: selectedCopyCount,
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    if (errEl) {
                        errEl.classList.add("hidden");
                        errEl.textContent = "";
                    }
                    stateMachine.setState(stateMachine.STATES.CAPTURE);
                } else {
                    if (errEl) {
                        errEl.textContent =
                            data.message || "Voucher tidak valid";
                        errEl.classList.remove("hidden");
                    }
                }
            } catch (err) {
                console.error("Apply voucher error", err);
                if (errEl) {
                    errEl.textContent = "Gagal memproses voucher";
                    errEl.classList.remove("hidden");
                }
            }
        });

    document
        .getElementById("btn-payment-qris")
        ?.addEventListener("click", async () => {
            if (!createPaymentUrl) return;
            const btn = document.getElementById("btn-payment-qris");
            if (btn) btn.disabled = true;
            try {
                const res = await fetch(createPaymentUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify({ copy_count: selectedCopyCount }),
                });
                const data = await res.json().catch(() => ({}));
                if (data.snap_token) {
                    if (btn) btn.disabled = false;
                    return;
                }
                const redirectUrl = data.redirect_url;
                if (!redirectUrl) {
                    if (btn) btn.disabled = false;
                    return;
                }
                window.location.href = redirectUrl;
            } catch (err) {
                console.error("Create payment error", err);
                if (btn) btn.disabled = false;
            }
        });

    // ─────────────────────────────────────────────────────────
    // CAPTURE SCREEN
    // ─────────────────────────────────────────────────────────

    document
        .getElementById("btn-capture-back")
        ?.addEventListener("click", () => {
            camera?.stop();
            stateMachine.setState(stateMachine.STATES.FRAME);
        });

    document
        .getElementById("btn-capture-next")
        ?.addEventListener("click", () => {
            const photos = camera?.getPhotos() || [];
            const maxPhotos = selectedFrameData?.photo_slots?.length ?? 1;
            if (photos.length < maxPhotos) return;
            stateMachine.setState(stateMachine.STATES.PREVIEW);
        });

    // ─────────────────────────────────────────────────────────
    // PREVIEW SCREEN
    // ─────────────────────────────────────────────────────────

    function renderPreviewPhotos(photos) {
        const grid = document.getElementById("preview-photo-grid");
        if (!grid) return;
        grid.innerHTML = "";
        photos.forEach((dataUrl, i) => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = `-scale-x-100 preview-photo-btn rounded-lg overflow-hidden border-2 transition-all ${i === selectedPhotoIndex ? "border-gray-900" : "border-transparent"}`;
            btn.dataset.index = i;
            const img = document.createElement("img");
            img.src = dataUrl;
            img.alt = `Photo ${i + 1}`;
            img.className = "w-full aspect-4/3 object-cover -scale-x-100";
            btn.appendChild(img);
            btn.addEventListener("click", () => {
                selectedPhotoIndex = i;
                grid.querySelectorAll(".preview-photo-btn").forEach((b) =>
                    b.classList.remove("border-gray-900"),
                );
                btn.classList.add("border-gray-900");
                if (selectedFrameData && photos[i]) {
                    renderPreviewLayout(selectedFrameData.frame_file);
                }
            });
            grid.appendChild(btn);
        });
    }

    async function renderPreviewLayout(frameUrl) {
        const merged = document.getElementById("preview-merged");
        if (!merged) return;
        merged.innerHTML = '<p class="text-gray-500 text-sm">Merging...</p>';
        try {
            const photos = camera?.getPhotos() || [];
            const slots = selectedFrameData?.photo_slots ?? [];
            const templateWidth = selectedFrameData?.template_width || 945;
            const templateHeight = selectedFrameData?.template_height;
            const mirrorChecked =
                document.getElementById("preview-mirror")?.checked ?? false;
            const photoSlots = slots.map((s) => ({
                x: s.x,
                y: s.y,
                width: s.width,
                height: s.height,
                canvasWidth: templateWidth,
                canvasHeight: templateHeight,
                templateWidth,
                templateHeight,
            }));
            const dataUrl = await mergePhotoWithFrame(photos, frameUrl, {
                photoSlots,
                mirror: mirrorChecked,
                photoLayer: selectedFrameData?.photo_layer ?? "behind",
            });
            merged.innerHTML = "";
            const img = document.createElement("img");
            img.src = dataUrl;
            img.alt = "Preview";
            img.className = "w-full h-full object-contain";
            merged.appendChild(img);
            img.onload = () => {
                merged.style.aspectRatio = `${img.naturalWidth / img.naturalHeight}`;
            };

            const printBtn = document.getElementById("btn-preview-print");
            if (printBtn) {
                printBtn.disabled = false;
                printBtn.classList.remove(
                    "bg-gray-200",
                    "text-gray-500",
                    "cursor-not-allowed",
                );
                printBtn.classList.add(
                    "bg-blue-600",
                    "text-white",
                    "cursor-pointer",
                );
            }
        } catch (err) {
            console.error("Merge error:", err);
            merged.innerHTML =
                '<p class="text-red-400 text-sm">Merge failed</p>';
        }
    }

    document
        .getElementById("preview-mirror")
        ?.addEventListener("change", () => {
            const photos = camera?.getPhotos() || [];
            if (photos[selectedPhotoIndex] && selectedFrameData) {
                renderPreviewLayout(selectedFrameData.frame_file);
            }
        });

    async function mergeForPrint(frameUrl) {
        const photos = camera?.getPhotos() || [];
        const slots = selectedFrameData?.photo_slots ?? [];
        const templateWidth = selectedFrameData?.template_width || 945;
        const templateHeight = selectedFrameData?.template_height;
        const mirrorChecked =
            document.getElementById("preview-mirror")?.checked ?? false;
        const photoSlots = slots.map((s) => ({
            x: s.x,
            y: s.y,
            width: s.width,
            height: s.height,
            canvasWidth: templateWidth,
            canvasHeight: templateHeight,
            templateWidth,
            templateHeight,
        }));
        return mergePhotoWithFrame(photos, frameUrl, {
            photoSlots,
            mirror: mirrorChecked,
            photoLayer: selectedFrameData?.photo_layer ?? "behind",
        });
    }

    async function uploadMediaOnce() {
        if (isUploaded) return;
        const photos = camera?.getPhotos() || [];
        try {
            if (mergedImageDataUrl) {
                await session.saveMedia("strip", mergedImageDataUrl);
            }
            for (let i = 0; i < photos.length; i++) {
                await session.saveMedia("image", photos[i], i + 1);
            }
            isUploaded = true;
        } catch (err) {
            console.error("Upload error:", err);
            throw err;
        }
    }

    document
        .getElementById("btn-preview-back")
        ?.addEventListener("click", () => {
            stateMachine.setState(stateMachine.STATES.CAPTURE);
        });

    document
        .getElementById("btn-preview-print")
        ?.addEventListener("click", async () => {
            const photos = camera?.getPhotos() || [];
            const photoUrl = photos[selectedPhotoIndex];
            if (!photoUrl || !selectedFrameData) return;

            const printBtn = document.getElementById("btn-preview-print");
            if (printBtn) {
                printBtn.disabled = true;
                printBtn.textContent = "Memproses...";
            }

            try {
                mergedImageDataUrl = await mergeForPrint(
                    selectedFrameData.frame_file,
                );
            } catch (err) {
                console.error("Merge error:", err);
                if (printBtn) {
                    printBtn.disabled = false;
                    printBtn.textContent = "Lanjut ke Print";
                }
                return;
            }
            if (printBtn) printBtn.textContent = "Lanjut ke Print";

            try {
                await uploadMediaOnce();
            } catch (err) {
                console.error("Save media error:", err);
            }

            const copies = selectedCopyCount || setting.copies || 1;
            if (isPrinterConnected()) {
                try {
                    await printPhotostrip(mergedImageDataUrl, copies);
                } catch (e) {
                    console.warn("Print failed:", e);
                }
            } else {
                console.warn("Printer tidak terhubung, skip printing");
            }

            stateMachine.setState(stateMachine.STATES.RESULT);
        });

    // ─────────────────────────────────────────────────────────
    // PRINT SCREEN
    // ─────────────────────────────────────────────────────────

    async function doPrint() {
        if (!mergedImageDataUrl) return;
        try {
            await uploadMediaOnce();
        } catch (err) {
            console.error("Save media error:", err);
        }

        const copies = selectedCopyCount || setting.copies || 1;
        try {
            const printWindow = window.open("", "_blank");
            if (printWindow) {
                printWindow.document
                    .write(`<!DOCTYPE html><html><head><title>Print</title>
          <style>@page{size:8cm 11cm;margin:0}body{margin:0}img{width:8cm;height:11cm;object-fit:contain}</style>
          </head><body><img src="${mergedImageDataUrl}" alt="Photostrip"></body></html>`);
                printWindow.document.close();
                printWindow.onload = () => {
                    printWindow.print();
                    setTimeout(() => printWindow.close(), 500);
                };
            }
        } catch (e) {
            console.warn("Print failed:", e);
        }

        document.getElementById("print-status")?.classList.add("hidden");
        document.getElementById("print-done")?.classList.remove("hidden");
    }

    document.getElementById("btn-print-next")?.addEventListener("click", () => {
        stateMachine.setState(stateMachine.STATES.RESULT);
    });

    // ─────────────────────────────────────────────────────────
    // RESULT SCREEN
    // ─────────────────────────────────────────────────────────

    // ─────────────────────────────────────────────────────────
    // RESULT TIMER
    // ─────────────────────────────────────────────────────────

    function stopResultTimer() {
        if (_resultTimerInterval) {
            clearInterval(_resultTimerInterval);
            _resultTimerInterval = null;
        }
    }

    function startResultTimer(seconds, onExpire) {
        stopResultTimer();

        const countEl = document.getElementById("result-timer-count");
        const barEl = document.getElementById("result-timer-bar");

        let remaining = seconds;

        // Set initial state
        if (countEl) countEl.textContent = remaining;
        if (barEl) {
            // Matikan transition dulu agar reset instan
            barEl.style.transition = "none";
            barEl.style.width = "100%";
            // Paksa reflow lalu aktifkan transition
            void barEl.offsetWidth;
            barEl.style.transition = "width 1s linear";
        }

        _resultTimerInterval = setInterval(() => {
            remaining -= 1;

            if (countEl) countEl.textContent = remaining;
            if (barEl) {
                const pct = Math.max(0, (remaining / seconds) * 100);
                barEl.style.width = pct + "%";

                // Ganti warna bar saat mendekati habis
                if (remaining <= 10) {
                    barEl.style.background = "var(--danger)";
                } else if (remaining <= 20) {
                    barEl.style.background = "#f97316"; // oranye
                } else {
                    barEl.style.background = "var(--primary)";
                }
            }

            if (remaining <= 0) {
                stopResultTimer();
                onExpire();
            }
        }, 1000);
    }

    async function handleResultPage() {
        const uploadStatus = document.getElementById("result-upload-status");
        const qrSection = document.getElementById("result-qr-section");
        const resultError = document.getElementById("result-error");
        const resultActions = document.getElementById("result-actions");
        const qrContainer = document.getElementById("result-qr-code");
        const resultUrlEl = document.getElementById("result-url");

        if (!uploadStatus || !qrSection) return;

        const photos = camera?.getPhotos() || [];
        try {
            await uploadMediaOnce();
        } catch (err) {
            console.error("Upload error:", err);
            uploadStatus.classList.add("hidden");
            if (resultError) {
                resultError.classList.remove("hidden");
                resultError.textContent = `Gagal mengupload foto: ${err.message || "Unknown error"}`;
            }
            return;
        }

        const sessionId = document.body.dataset.sessionId;
        const finalResultUrl =
            resultUrl || `${window.location.origin}/booth/result/${sessionId}`;

        uploadStatus.classList.add("hidden");
        qrSection.classList.remove("hidden");
        if (resultActions) resultActions.classList.remove("hidden");

        try {
            await session.updateSession({ status: "completed" });
        } catch (err) {
            console.warn("Gagal update status session:", err);
        }

        if (qrContainer) {
            qrContainer.innerHTML = "";
            const qrImg = document.createElement("img");
            qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(finalResultUrl)}`;
            qrImg.alt = "QR Code";
            qrImg.className = "w-full max-w-xs";
            qrContainer.appendChild(qrImg);
        }

        if (resultUrlEl) resultUrlEl.textContent = finalResultUrl;

        // Mulai timer — redirect ke welcome saat habis
        startResultTimer(RESULT_TIMEOUT_SECONDS, () => {
            const projectId = document.body.dataset.projectId;
            if (projectId) {
                window.location.href = `/booth/${projectId}`;
            } else {
                stateMachine.setState(stateMachine.STATES.RESET);
            }
        });
    }

    document
        .getElementById("btn-result-home")
        ?.addEventListener("click", () => {
            stopResultTimer();
            const projectId = document.body.dataset.projectId;
            if (projectId) {
                window.location.href = `/booth/${projectId}`;
            } else {
                stateMachine.setState(stateMachine.STATES.RESET);
            }
        });

    // ─────────────────────────────────────────────────────────
    // QR / DONE SCREEN
    // ─────────────────────────────────────────────────────────

    stateMachine.subscribe((state) => {
        if (state === stateMachine.STATES.DONE && resultUrl) {
            const qrContainer = document.getElementById("qr-container");
            if (qrContainer && !qrContainer.querySelector("img")) {
                const img = document.createElement("img");
                img.src = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(resultUrl)}`;
                img.alt = "QR Code";
                img.className = "block";
                qrContainer.appendChild(img);
            }
        }
    });

    document.getElementById("btn-reset")?.addEventListener("click", () => {
        const projectId = document.body.dataset.projectId;
        if (projectId) {
            window.location.href = `/booth/${projectId}`;
        } else {
            window.location.reload();
        }
    });

    // ─────────────────────────────────────────────────────────
    // FULLSCREEN & CAMERA SETTINGS
    // ─────────────────────────────────────────────────────────

    document
        .getElementById("btn-lock-fullscreen")
        ?.addEventListener("click", () => {
            if (
                document.fullscreenElement ||
                document.webkitFullscreenElement
            ) {
                if (document.exitFullscreen) document.exitFullscreen();
                else if (document.webkitExitFullscreen)
                    document.webkitExitFullscreen();
            } else {
                const el = document.documentElement;
                if (el.requestFullscreen)
                    el.requestFullscreen().catch(() => {});
                else if (el.webkitRequestFullscreen)
                    el.webkitRequestFullscreen();
            }
        });

    initCameraSettings();
    stateMachine.setState(initialState);
});
