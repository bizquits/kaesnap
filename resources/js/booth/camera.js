/**
 * Camera and countdown logic.
 * Uses saved device from Camera Setting modal (localStorage photobooth_selectedCameraId).
 */

import { isAndroid } from "./camera-utils.js";

const STORAGE_KEY = "photobooth_selectedCameraId";

// ─────────────────────────────────────────────────────────
// AUDIO
// ─────────────────────────────────────────────────────────

let _audioCtx = null;

function getAudioCtx() {
    if (!_audioCtx) {
        _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    // Resume jika suspended (browser autoplay policy)
    if (_audioCtx.state === "suspended") {
        _audioCtx.resume();
    }
    return _audioCtx;
}

/**
 * Mainkan nada pendek.
 * @param {number} freq - Frekuensi Hz
 * @param {"sine"|"triangle"|"square"|"sawtooth"} type - Waveform
 * @param {number} duration - Durasi detik
 * @param {number} volume - 0–1
 */
function playBeep(freq = 880, type = "sine", duration = 0.12, volume = 0.4) {
    try {
        const ctx = getAudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = type;
        osc.frequency.setValueAtTime(freq, ctx.currentTime);

        // Envelope: fade in cepat, fade out smooth
        gain.gain.setValueAtTime(0, ctx.currentTime);
        gain.gain.linearRampToValueAtTime(volume, ctx.currentTime + 0.01);
        gain.gain.exponentialRampToValueAtTime(
            0.001,
            ctx.currentTime + duration,
        );

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + duration);
    } catch (e) {
        console.warn("[audio] playBeep error:", e);
    }
}

/** Beep countdown: nada tinggi singkat */
function playCountdownBeep() {
    playBeep(880, "sine", 0.12, 0.35);
}

/** Beep capture: freq 520 triangle sesuai permintaan */
function playCaptureBeep() {
    playBeep(520, "triangle", 0.22, 0.45);
}

// ─────────────────────────────────────────────────────────
// FLASH EFFECT
// ─────────────────────────────────────────────────────────

/**
 * Tampilkan flash putih sesaat di atas layar saat foto diambil.
 * Membuat elemen overlay sementara jika belum ada.
 */
function triggerFlash() {
    let flash = document.getElementById("capture-flash-overlay");
    if (!flash) {
        flash = document.createElement("div");
        flash.id = "capture-flash-overlay";
        flash.style.cssText = [
            "position:fixed",
            "inset:0",
            "background:#fff",
            "opacity:0",
            "pointer-events:none",
            "z-index:9999",
            "transition:opacity 0.05s ease-in",
        ].join(";");
        document.body.appendChild(flash);
    }

    // Reset animasi
    flash.style.transition = "none";
    flash.style.opacity = "1";

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            flash.style.transition = "opacity 0.35s ease-out";
            flash.style.opacity = "0";
        });
    });
}

// ─────────────────────────────────────────────────────────
// CROP HELPER
// ─────────────────────────────────────────────────────────

/** Aspect ratio 4:3 (landscape) sesuai preview. */
function cropVideoToLandscape(video) {
    const aspect = 4 / 3;
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    const va = vw / vh;
    let sx = 0,
        sy = 0,
        sw = vw,
        sh = vh;
    if (va > aspect) {
        sw = vh * aspect;
        sx = (vw - sw) / 2;
    } else {
        sh = vw / aspect;
        sy = (vh - sh) / 2;
    }
    return { x: sx, y: sy, width: sw, height: sh };
}

// ─────────────────────────────────────────────────────────
// MAIN
// ─────────────────────────────────────────────────────────

export function createCamera(session, options = {}) {
    const video = document.getElementById("video-preview");
    const canvas = document.getElementById("capture-canvas");
    const countdownOverlay = document.getElementById("countdown-overlay");
    const countdownNumber = document.getElementById("countdown-number");
    const cameraStartOverlay = document.getElementById("camera-start-overlay");
    const captureStartOverlay = document.getElementById(
        "capture-start-overlay",
    );
    const capturePhotos = document.getElementById("capture-photos");
    const captureEmpty = document.getElementById("capture-empty");
    const captureStatus = document.getElementById("capture-status");
    const btnStartCamera = document.getElementById("btn-start-camera");
    const btnStartCapture = document.getElementById("btn-start-capture");
    const exposureSlider = document.getElementById("capture-exposure");
    const btnBack = document.getElementById("btn-capture-back");
    const btnNext = document.getElementById("btn-capture-next");

    if (!video || !canvas) return null;

    let stream = null;
    let photos = [];
    let isActive = false;
    let isLoading = false;
    let hasStarted = false;
    let isCapturing = false;
    let exposureMin = -2;
    let exposureMax = 2;
    let exposureSupported = false;
    const maxPhotos = parseInt(options.maxPhotos, 10) || 1;
    const countdownDelay = Math.min(
        10,
        Math.max(1, parseInt(options.countdownSeconds, 10) || 3),
    );

    function stopStream() {
        if (stream) {
            stream.getTracks().forEach((t) => t.stop());
            stream = null;
        }
        if (video.srcObject) video.srcObject = null;
        if (video.src) video.src = "";
        if (video) video.style.filter = "";
        isActive = false;
        hasStarted = false;
    }

    async function startStream() {
        if (isLoading || isActive) return;
        const deviceId = localStorage.getItem(STORAGE_KEY) || null;

        const useFacingMode =
            isAndroid() ||
            deviceId === "android-default" ||
            deviceId === "android-user" ||
            deviceId === "android-environment";
        const facingMode =
            deviceId === "android-user"
                ? "user"
                : deviceId === "android-environment"
                  ? "environment"
                  : "environment";
        const constraints = useFacingMode
            ? { video: { facingMode, aspectRatio: 4 / 3 }, audio: false }
            : {
                  video: {
                      deviceId: deviceId ? { ideal: deviceId } : true,
                      aspectRatio: 4 / 3,
                  },
                  audio: false,
              };

        try {
            isLoading = true;
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            video.src = "";
            await video.play();
            isActive = true;
            isLoading = false;
            cameraStartOverlay?.classList.add("hidden");
            cameraStartOverlay?.classList.remove("flex");
            captureStartOverlay?.classList.remove("hidden");
            captureStartOverlay?.classList.add("flex");
            setupExposureControl(stream);
            applyExposureFromSlider();
        } catch (err) {
            console.error("Camera error:", err);
            isLoading = false;
            alert(
                "Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.",
            );
        }
    }

    function setupExposureControl(stream) {
        const track = stream.getVideoTracks()[0];
        if (!track || !exposureSlider) return;
        exposureSlider.disabled = false;
        const caps = track.getCapabilities?.();
        if (caps && typeof caps.exposureCompensation !== "undefined") {
            const r = caps.exposureCompensation;
            exposureMin = r.min ?? -2;
            exposureMax = r.max ?? 2;
            exposureSupported = true;
            exposureSlider.title = `Exposure (${exposureMin} … ${exposureMax})`;
            applyExposureFromSlider();
        } else {
            exposureSupported = false;
            exposureSlider.title = "Exposure: tidak didukung kamera ini";
        }
    }

    function applyExposureFromSlider() {
        if (!stream || !exposureSlider) return;
        const raw = Number(exposureSlider.value);
        const brightness = 0.3 + (raw / 100) * 1.7;
        const contrast = 0.7 + (raw / 100) * 0.6;
        const filterValue = `brightness(${brightness}) contrast(${contrast})`;
        if (video) video.style.filter = filterValue;

        if (exposureSupported) {
            const track = stream.getVideoTracks()[0];
            if (!track) return;
            const value =
                exposureMin + (raw / 100) * (exposureMax - exposureMin);
            track
                .applyConstraints({
                    advanced: [{ exposureCompensation: value }],
                })
                .catch(() => {});
        }
    }

    function captureFromVideo() {
        if (!video.videoWidth || !canvas) return null;
        const crop = cropVideoToLandscape(video);
        canvas.width = crop.width;
        canvas.height = crop.height;
        const ctx = canvas.getContext("2d");
        if (!ctx) return null;

        if (!exposureSupported && exposureSlider) {
            const raw = Number(exposureSlider.value);
            const brightness = 0.3 + (raw / 100) * 1.7;
            const contrast = 0.7 + (raw / 100) * 0.6;
            ctx.filter = `brightness(${brightness}) contrast(${contrast})`;
        }

        ctx.save();
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(
            video,
            crop.x,
            crop.y,
            crop.width,
            crop.height,
            0,
            0,
            canvas.width,
            canvas.height,
        );
        ctx.restore();
        ctx.filter = "none";

        return canvas.toDataURL("image/png");
    }

    function updatePhotoUI() {
        if (!capturePhotos || !captureEmpty) return;
        captureEmpty.classList.toggle("hidden", photos.length > 0);
        captureStatus.textContent = `${photos.length}/${maxPhotos} photos`;
        btnNext.disabled = photos.length < maxPhotos;
        btnNext.classList.toggle("opacity-50", photos.length < maxPhotos);
        btnNext.classList.toggle(
            "cursor-not-allowed",
            photos.length < maxPhotos,
        );
        if (photos.length >= maxPhotos) {
            btnNext.classList.remove("bg-gray-200", "text-gray-500");
            btnNext.classList.add(
                "bg-blue-600",
                "text-white",
                "cursor-pointer",
            );
        } else {
            btnNext.classList.add("bg-gray-200", "text-gray-500");
            btnNext.classList.remove(
                "bg-blue-600",
                "text-white",
                "cursor-pointer",
            );
        }

        const existing = capturePhotos.querySelectorAll(".capture-photo-item");
        existing.forEach((el) => el.remove());
        photos.forEach((dataUrl, i) => {
            const div = document.createElement("div");
            div.className = "capture-photo-item relative";
            const img = document.createElement("img");
            img.src = dataUrl;
            img.alt = `Photo ${i + 1}`;
            img.className = "w-full rounded-lg object-contain";
            div.appendChild(img);
            const retake = document.createElement("button");
            retake.type = "button";
            retake.className =
                "absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 hover:opacity-100 rounded-lg";
            retake.innerHTML =
                '<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
            retake.onclick = () => retakePhoto(i);
            div.appendChild(retake);
            capturePhotos.appendChild(div);
        });
    }

    /**
     * Countdown dengan beep di setiap angka.
     */
    async function doCountdown(callback) {
        if (!countdownOverlay || !countdownNumber) return callback?.();
        captureStartOverlay?.classList.add("hidden");
        captureStartOverlay?.classList.remove("flex");
        countdownOverlay.classList.remove("hidden");
        countdownOverlay.classList.add("flex");

        for (let i = countdownDelay; i > 0; i--) {
            countdownNumber.textContent = i;
            playCountdownBeep(); // ← beep setiap angka
            await new Promise((r) => setTimeout(r, 1000));
        }

        countdownOverlay.classList.add("hidden");
        countdownOverlay.classList.remove("flex");
        captureStartOverlay?.classList.remove("hidden");
        captureStartOverlay?.classList.add("flex");
        callback?.();
    }

    function retakePhoto(index) {
        if (isCapturing) return;
        isCapturing = true;
        doCountdown(() => {
            playCaptureBeep(); // ← beep capture
            triggerFlash(); // ← flash putih
            const dataUrl = captureFromVideo();
            if (dataUrl) {
                photos[index] = dataUrl;
                updatePhotoUI();
            }
            isCapturing = false;
        });
    }

    function startCaptureSequence() {
        if (
            !isActive ||
            hasStarted ||
            isCapturing ||
            photos.length >= maxPhotos
        )
            return;
        hasStarted = true;
        isCapturing = true;

        const captureOne = () => {
            if (photos.length >= maxPhotos) {
                isCapturing = false;
                updatePhotoUI();
                return;
            }
            doCountdown(() => {
                playCaptureBeep(); // ← beep capture
                triggerFlash(); // ← flash putih
                const dataUrl = captureFromVideo();
                if (dataUrl) {
                    photos.push(dataUrl);
                    updatePhotoUI();
                }
                isCapturing = false;
                if (photos.length < maxPhotos) {
                    setTimeout(captureOne, 1000);
                }
            });
        };
        setTimeout(captureOne, 500);
    }

    btnStartCamera?.addEventListener("click", startStream);
    btnStartCapture?.addEventListener("click", startCaptureSequence);
    exposureSlider?.addEventListener("input", applyExposureFromSlider);

    return {
        stop: stopStream,
        getPhotos: () => [...photos],
        setPhotos: (p) => {
            photos = p || [];
            updatePhotoUI();
        },
        reset: () => {
            photos = [];
            hasStarted = false;
            updatePhotoUI();
        },
    };
}
