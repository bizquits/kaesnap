/**
 * Camera Settings Module (Pengaturan Perangkat)
 * Camera, printer (WebUSB/BLE).
 * PIN gate: user harus input PIN yang benar sebelum melihat settings.
 */

import { enumerateCameras, isAndroid } from "./camera-utils.js";
import {
    connectPrinterUSB,
    connectPrinterBLE,
    disconnectPrinter,
    isPrinterConnected,
    isWebUSBAvailable,
    isWebBluetoothAvailable,
} from "./printer.js";

const STORAGE_KEY = "photobooth_selectedCameraId";
const PRINTER_TYPE_KEY = "photobooth_printerType";
const PIN_LENGTH = 4;

// PIN diambil dari data attribute body: data-settings-pin="1234"
// Fallback default jika tidak ada: "1234"
function getCorrectPin() {
    return (document.body.dataset.settingsPin || "1234").trim();
}

// ===========================
// DOM HELPERS
// ===========================

const $ = (id) => document.getElementById(id);

function getModalEl() {
    return $("camera-settings-modal");
}
function getPinPanel() {
    return $("camera-settings-pin-panel");
}
function getContentPanel() {
    return $("camera-settings-content-panel");
}
function getSelectEl() {
    return $("camera-settings-select");
}
function getPreviewEl() {
    return $("camera-settings-preview");
}
function getPrinterStatusEl() {
    return $("camera-settings-printer-status");
}
function getPrinterTypeSelect() {
    return $("camera-settings-printer-type");
}

// ===========================
// PIN STATE
// ===========================

let pinDigits = [];

function getPinDots() {
    return document.querySelectorAll("#camera-settings-pin-dots .pin-dot");
}

function updatePinDots() {
    getPinDots().forEach((dot, i) => {
        if (i < pinDigits.length) {
            dot.style.background = "var(--primary)";
            dot.style.transform = "scale(1.15)";
        } else {
            dot.style.background = "var(--border)";
            dot.style.transform = "scale(1)";
        }
    });
}

function resetPin() {
    pinDigits = [];
    updatePinDots();
    const errEl = $("camera-settings-pin-error");
    if (errEl) errEl.classList.add("hidden");
}

/** Shake animation saat PIN salah */
function shakePinDots() {
    const wrap = $("camera-settings-pin-dots");
    if (!wrap) return;
    wrap.style.transition = "transform 0.07s ease";
    const steps = [6, -6, 5, -5, 3, 0];
    let i = 0;
    const next = () => {
        if (i >= steps.length) {
            wrap.style.transform = "";
            return;
        }
        wrap.style.transform = `translateX(${steps[i]}px)`;
        i++;
        setTimeout(next, 60);
    };
    next();
}

function handleDigitInput(digit) {
    if (pinDigits.length >= PIN_LENGTH) return;

    pinDigits.push(digit);
    updatePinDots();

    if (pinDigits.length === PIN_LENGTH) {
        const entered = pinDigits.join("");
        setTimeout(() => checkPin(entered), 120); // sedikit delay agar dot ke-4 terlihat
    }
}

function handleBackspace() {
    if (pinDigits.length === 0) return;
    pinDigits.pop();
    updatePinDots();
    const errEl = $("camera-settings-pin-error");
    if (errEl) errEl.classList.add("hidden");
}

function checkPin(entered) {
    if (entered === getCorrectPin()) {
        showContentPanel();
    } else {
        shakePinDots();
        const errEl = $("camera-settings-pin-error");
        if (errEl) errEl.classList.remove("hidden");
        // Reset setelah animasi
        setTimeout(resetPin, 600);
    }
}

// ===========================
// PANEL SWITCHING
// ===========================

function showPinPanel() {
    const pin = getPinPanel();
    const content = getContentPanel();
    if (pin) {
        pin.classList.remove("hidden");
    }
    if (content) {
        content.classList.add("hidden");
    }
    resetPin();
}

function showContentPanel() {
    const pin = getPinPanel();
    const content = getContentPanel();
    if (pin) {
        pin.classList.add("hidden");
    }
    if (content) {
        content.classList.remove("hidden");
    }

    // Muat kamera dan update printer status
    loadCameras().then((selectedId) => {
        if (selectedId) startPreview(selectedId);
    });
    updatePrinterTypeSelect();
    updatePrinterStatus();

    const select = getSelectEl();
    if (select) select.addEventListener("change", onSelectChange);
}

// ===========================
// PREVIEW STREAM
// ===========================

let previewStream = null;

function getPlaceholderEl() {
    return $("camera-settings-placeholder");
}

function stopPreviewStream() {
    if (previewStream) {
        previewStream.getTracks().forEach((t) => t.stop());
        previewStream = null;
    }
    const video = getPreviewEl();
    if (video) {
        video.srcObject = null;
        video.src = "";
    }
    const placeholder = getPlaceholderEl();
    if (placeholder) placeholder.style.display = "";
}

function getFacingModeFromValue(value) {
    if (value === "android-user") return "user";
    if (value === "android-environment") return "environment";
    return "environment";
}

function startPreview(deviceId) {
    stopPreviewStream();
    const video = getPreviewEl();
    if (!video) return;

    const useFacingMode =
        isAndroid() ||
        deviceId === "android-default" ||
        deviceId === "android-user" ||
        deviceId === "android-environment";

    const constraints = useFacingMode
        ? {
              video: {
                  facingMode: getFacingModeFromValue(deviceId),
                  aspectRatio: 4 / 3,
              },
              audio: false,
          }
        : {
              video: {
                  deviceId: deviceId ? { ideal: deviceId } : true,
                  aspectRatio: 4 / 3,
              },
              audio: false,
          };

    const placeholder = getPlaceholderEl();
    if (placeholder) placeholder.style.display = "";

    navigator.mediaDevices
        .getUserMedia(constraints)
        .then((stream) => {
            previewStream = stream;
            video.srcObject = stream;
            video.src = "";
            if (placeholder) placeholder.style.display = "none";
            video
                .play()
                .catch((err) =>
                    console.warn("[camera-settings] video play failed:", err),
                );
        })
        .catch((err) => {
            console.error("[camera-settings] preview failed:", err);
            if (placeholder) placeholder.style.display = "";
        });
}

// ===========================
// CAMERA ENUMERATION
// ===========================

function populateSelect(devices, savedDeviceId) {
    const select = getSelectEl();
    if (!select) return null;

    select.innerHTML = "";
    let selectedId = savedDeviceId;

    if (isAndroid()) {
        const optBack = document.createElement("option");
        optBack.value = "android-environment";
        optBack.textContent = "Kamera Belakang";
        select.appendChild(optBack);
        const optFront = document.createElement("option");
        optFront.value = "android-user";
        optFront.textContent = "Kamera Depan";
        select.appendChild(optFront);
        if (
            savedDeviceId === "android-user" ||
            savedDeviceId === "android-environment"
        ) {
            selectedId = savedDeviceId;
        } else {
            selectedId = "android-environment";
        }
    } else {
        if (devices.length === 0) {
            const optNone = document.createElement("option");
            optNone.value = "";
            optNone.textContent = "No cameras found";
            select.appendChild(optNone);
            return null;
        }
        devices.forEach((cam, i) => {
            const opt = document.createElement("option");
            opt.value = cam.deviceId;
            opt.textContent = cam.label || `Camera ${i + 1}`;
            select.appendChild(opt);
        });
        if (selectedId && !devices.find((d) => d.deviceId === selectedId)) {
            selectedId = devices[0]?.deviceId || null;
        }
        if (!selectedId && devices.length) selectedId = devices[0].deviceId;
    }

    select.value = selectedId || "";
    return select.value;
}

function loadCameras() {
    return enumerateCameras().then((devices) => {
        const saved = localStorage.getItem(STORAGE_KEY);
        const selectedId = populateSelect(devices, saved);
        if (selectedId) localStorage.setItem(STORAGE_KEY, selectedId);
        return selectedId;
    });
}

function onSelectChange() {
    const select = getSelectEl();
    if (!select) return;
    const newId = select.value;
    if (newId) localStorage.setItem(STORAGE_KEY, newId);
    startPreview(newId);
}

// ===========================
// MODAL OPEN / CLOSE
// ===========================

function openModal() {
    const modal = getModalEl();
    if (!modal) return;

    modal.classList.remove("hidden");
    modal.classList.add("flex");
    modal.setAttribute("aria-hidden", "false");

    // Selalu mulai dari PIN panel
    showPinPanel();
}

function closeModal() {
    const modal = getModalEl();
    if (!modal) return;

    modal.classList.add("hidden");
    modal.classList.remove("flex");
    modal.setAttribute("aria-hidden", "true");

    stopPreviewStream();

    const select = getSelectEl();
    if (select) select.removeEventListener("change", onSelectChange);

    // Reset ke PIN panel untuk sesi berikutnya
    showPinPanel();
}

// ===========================
// PRINTER
// ===========================

function updatePrinterTypeSelect() {
    const sel = getPrinterTypeSelect();
    if (!sel) return;

    const usbAvailable = isWebUSBAvailable();
    const btAvailable = isWebBluetoothAvailable();
    const saved = localStorage.getItem(PRINTER_TYPE_KEY) || "bluetooth";

    sel.innerHTML = "";
    if (btAvailable) {
        const optBt = document.createElement("option");
        optBt.value = "bluetooth";
        optBt.textContent = "Bluetooth (BLE)";
        sel.appendChild(optBt);
    }
    if (usbAvailable) {
        const optUsb = document.createElement("option");
        optUsb.value = "usb";
        optUsb.textContent = "USB";
        sel.appendChild(optUsb);
    }
    if (!btAvailable && !usbAvailable) {
        const optNone = document.createElement("option");
        optNone.value = "";
        optNone.textContent = "Tidak didukung";
        sel.appendChild(optNone);
        const btnConnect = $("camera-settings-connect-printer");
        if (btnConnect) btnConnect.disabled = true;
        return;
    }

    const validSaved =
        (saved === "bluetooth" && btAvailable) ||
        (saved === "usb" && usbAvailable);
    sel.value = validSaved ? saved : btAvailable ? "bluetooth" : "usb";
}

function updatePrinterStatus() {
    const statusEl = getPrinterStatusEl();
    const btnEl = $("camera-settings-connect-printer");
    if (!statusEl || !btnEl) return;

    if (isPrinterConnected()) {
        statusEl.textContent = "Terhubung";
        statusEl.style.color = "var(--success)";
        btnEl.textContent = "Putuskan Printer";
        btnEl.dataset.action = "disconnect";
    } else {
        statusEl.textContent = "Tidak Terhubung";
        statusEl.style.color = "var(--danger)";
        btnEl.textContent = "Hubungkan Printer";
        btnEl.dataset.action = "connect";
    }
}

async function handleConnectPrinter() {
    const btnEl = $("camera-settings-connect-printer");
    const action = btnEl?.dataset.action || "connect";

    if (action === "disconnect") {
        try {
            btnEl.disabled = true;
            await disconnectPrinter();
            updatePrinterStatus();
        } catch (err) {
            console.error("Disconnect printer error:", err);
            alert(
                "Gagal memutuskan printer: " + (err.message || "Unknown error"),
            );
        } finally {
            btnEl.disabled = false;
        }
        return;
    }

    const printerType = getPrinterTypeSelect()?.value || "bluetooth";
    localStorage.setItem(PRINTER_TYPE_KEY, printerType);

    try {
        btnEl.disabled = true;
        if (printerType === "usb") {
            if (!isWebUSBAvailable()) {
                alert("WebUSB tidak didukung. Gunakan Chrome atau Edge.");
                return;
            }
            await connectPrinterUSB(0x0418);
        } else {
            if (!isWebBluetoothAvailable()) {
                alert(
                    "Web Bluetooth tidak didukung. Gunakan Chrome atau Edge.",
                );
                return;
            }
            await connectPrinterBLE();
        }
        updatePrinterStatus();
    } catch (err) {
        if (err.name === "NotFoundError") {
            alert(
                "Tidak ada printer yang dipilih atau printer tidak ditemukan.",
            );
        } else if (err.name === "SecurityError") {
            alert(
                "Akses ditolak. Pastikan izin untuk USB/Bluetooth diberikan.",
            );
        } else {
            console.error("Connect printer error:", err);
            alert(
                "Gagal menghubungkan printer: " +
                    (err.message || "Unknown error"),
            );
        }
    } finally {
        btnEl.disabled = false;
    }
}

// ===========================
// INITIALIZATION
// ===========================

export function initCameraSettings() {
    const modal = getModalEl();

    // ── Open ──
    $("btn-camera-settings")?.addEventListener("click", openModal);

    // ── Close: PIN panel ──
    $("camera-settings-pin-close")?.addEventListener("click", closeModal);

    // ── Close: Content panel ──
    $("camera-settings-close")?.addEventListener("click", closeModal);

    // ── Save ──
    $("camera-settings-save")?.addEventListener("click", () => {
        const select = getSelectEl();
        if (select?.value) localStorage.setItem(STORAGE_KEY, select.value);
        const printerTypeSel = getPrinterTypeSelect();
        if (printerTypeSel?.value)
            localStorage.setItem(PRINTER_TYPE_KEY, printerTypeSel.value);
        closeModal();
    });

    // ── Click outside to close ──
    modal?.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });

    // ── ESC to close ──
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !modal?.classList.contains("hidden"))
            closeModal();
    });

    // ── Printer ──
    $("camera-settings-connect-printer")?.addEventListener(
        "click",
        handleConnectPrinter,
    );

    // ── PIN numpad buttons ──
    document.querySelectorAll(".pin-numpad-btn").forEach((btn) => {
        btn.addEventListener("click", () =>
            handleDigitInput(btn.dataset.digit),
        );
    });

    // ── Backspace ──
    $("pin-backspace")?.addEventListener("click", handleBackspace);

    // ── Keyboard support (when modal is open) ──
    document.addEventListener("keydown", (e) => {
        if (modal?.classList.contains("hidden")) return;
        if (!getPinPanel() || getPinPanel().classList.contains("hidden"))
            return;

        if (e.key >= "0" && e.key <= "9") {
            handleDigitInput(e.key);
        } else if (e.key === "Backspace") {
            handleBackspace();
        }
    });
}

export function getSelectedCameraId() {
    return localStorage.getItem(STORAGE_KEY);
}
