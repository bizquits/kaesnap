{{--
    Custom QRIS payment page — Redesigned UI
    Clean, professional, minimalist — blue theme
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bayar – {{ $projectName }}</title>
    @vite(['resources/css/booth.css'])
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
        }

        .font-mono {
            font-family: 'DM Mono', monospace;
        }

        /* Background mesh */
        .bg-mesh {
            background-color: #fafaf9;
        }

        /* Card shimmer */
        .card-shimmer {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        /* QR border glow */
        .qr-border {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #6366f1 100%);
            padding: 3px;
            border-radius: 20px;
        }

        .qr-inner {
            background: #fff;
            border-radius: 17px;
            overflow: hidden;
        }

        /* Scanning animation */
        @keyframes scanline {
            0% {
                top: 0%;
                opacity: 1;
            }

            90% {
                top: 100%;
                opacity: 1;
            }

            100% {
                top: 100%;
                opacity: 0;
            }
        }

        .scan-line {
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #3b82f6, #6366f1, #3b82f6, transparent);
            animation: scanline 2.4s ease-in-out infinite;
            box-shadow: 0 0 12px 2px rgba(59, 130, 246, 0.45);
        }

        /* Pulse ring */
        @keyframes ping-slow {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }

            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }

        .pulse-ring {
            animation: ping-slow 2s ease-out infinite;
        }

        /* Spinner */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            animation: spin 0.9s linear infinite;
        }

        /* Status dot pulse */
        @keyframes dot-pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .status-dot {
            animation: dot-pulse 1.6s ease-in-out infinite;
        }

        /* Fade-in up */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease both;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }

        /* Corner marks on QR */
        .corner {
            position: absolute;
            width: 22px;
            height: 22px;
            border-color: #3b82f6;
            border-style: solid;
        }

        .corner-tl {
            top: 12px;
            left: 12px;
            border-width: 3px 0 0 3px;
            border-radius: 4px 0 0 0;
        }

        .corner-tr {
            top: 12px;
            right: 12px;
            border-width: 3px 3px 0 0;
            border-radius: 0 4px 0 0;
        }

        .corner-bl {
            bottom: 12px;
            left: 12px;
            border-width: 0 0 3px 3px;
            border-radius: 0 0 0 4px;
        }

        .corner-br {
            bottom: 12px;
            right: 12px;
            border-width: 0 3px 3px 0;
            border-radius: 0 0 4px 0;
        }

        /* Success state */
        @keyframes checkDraw {
            from {
                stroke-dashoffset: 60;
            }

            to {
                stroke-dashoffset: 0;
            }
        }

        .check-path {
            stroke-dasharray: 60;
            stroke-dashoffset: 60;
            animation: checkDraw 0.5s ease 0.2s forwards;
        }

        /* Sandbox section */
        .sandbox-card {
            background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
            border: 1px solid #fde68a;
        }

        /* Copy button feedback */
        .copy-btn {
            transition: all 0.2s ease;
        }

        .copy-btn:active {
            transform: scale(0.95);
        }


        /* Step indicators */
        .step-item {
            transition: all 0.3s ease;
        }

        /* Input focus */
        input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
    </style>
</head>

<body class="m-0 bg-mesh min-h-screen">
    <div class="relative min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">

        {{-- Main Card --}}
        <div class="card-shimmer fade-in-up delay-1 w-full max-w-md rounded-3xl shadow-2xl shadow-blue-100/60 border border-blue-100/80 overflow-hidden">

            <div class="p-8">

                {{-- Title --}}
                <div class="text-center mb-7">
                    <h1 class="text-xl font-semibold text-blue-950 tracking-tight mb-1">Scan QRIS untuk Membayar</h1>
                </div>

                {{-- QR Code Area --}}
                <div class="fade-in-up delay-2 flex justify-center mb-6">
                    <div id="pay-qris-qr-wrap" class="relative">

                        @if($qrImageDataUrl || $qrImageUrl)
                        {{-- QR Frame --}}
                        <div class="qr-border shadow-xl shadow-blue-200/50">
                            <div class="qr-inner relative">
                                @if($qrImageDataUrl)
                                <img src="{{ $qrImageDataUrl }}" alt="QRIS" class="w-72 h-72 block">
                                @else
                                <img id="pay-qris-img" src="{{ $qrImageUrl }}" alt="QRIS" class="w-72 h-72 block">
                                @endif

                                {{-- Corner marks --}}
                                <div class="corner corner-tl"></div>
                                <div class="corner corner-tr"></div>
                                <div class="corner corner-bl"></div>
                                <div class="corner corner-br"></div>
                            </div>
                        </div>


                        @else
                        {{-- Loading State --}}
                        <div class="qr-border">
                            <div class="qr-inner w-72 h-72 flex flex-col items-center justify-center gap-4 bg-gradient-to-br from-blue-50 to-indigo-50">
                                <div class="relative">
                                    <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
                                        <svg class="spinner w-7 h-7 text-blue-500" fill="none" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="32" stroke-dashoffset="12" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-medium text-blue-700">Memuat QR Code</p>
                                    <p class="text-xs text-blue-400 mt-0.5">Harap tunggu sebentar...</p>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                {{-- Amount --}}
                <div class="fade-in-up delay-3 text-center mb-4">
                    <p class="text-xs text-blue-950 uppercase tracking-widest font-medium mb-1">Total Pembayaran</p>
                    <p class="text-4xl font-semibold text-blue-950 tracking-tight" id="pay-qris-amount">{{ $amountFormatted }}</p>
                </div>

                {{-- Status --}}
                <div class="flex items-center justify-center gap-2 py-3 px-4 rounded-2xl bg-linear-to-r from-blue-50 to-indigo-50 border border-blue-100">
                    <div class="relative flex items-center justify-center">
                        <div class="w-2 h-2 rounded-full bg-blue-400 status-dot"></div>
                        <div class="absolute w-4 h-4 rounded-full bg-blue-200 pulse-ring"></div>
                    </div>
                    <p class="text-sm text-blue-600 font-medium" id="pay-qris-status">Menunggu pembayaran...</p>
                </div>

            </div>

        </div>

        {{-- Sandbox Testing Section --}}
        @if(!empty($isSandbox) && !empty($simulatorQrCodeUrl))
        <div class="fade-in-up delay-4 w-full max-w-md mt-4">
            <div class="sandbox-card rounded-2xl p-5">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-amber-900">Mode Sandbox</p>
                        <p class="text-xs text-amber-700 mt-0.5">Simulasi pembayaran untuk testing</p>
                    </div>
                    <span class="ml-auto shrink-0 text-xs font-medium bg-amber-200 text-amber-800 rounded-full px-2.5 py-0.5">DEV</span>
                </div>

                <div class="bg-white/70 rounded-xl p-3.5 mb-3 border border-amber-200/60">
                    <ol class="space-y-1.5">
                        <li class="flex items-start gap-2 text-xs text-amber-800">
                            <span class="shrink-0 w-4 h-4 rounded-full bg-amber-200 text-amber-800 text-[10px] font-bold flex items-center justify-center mt-0.5">1</span>
                            <span>Buka <a href="{{ $simulatorPageUrl }}" target="_blank" rel="noopener" class="font-semibold underline underline-offset-2">Simulator QRIS Midtrans</a> di tab baru</span>
                        </li>
                        <li class="flex items-start gap-2 text-xs text-amber-800">
                            <span class="shrink-0 w-4 h-4 rounded-full bg-amber-200 text-amber-800 text-[10px] font-bold flex items-center justify-center mt-0.5">2</span>
                            <span>Paste URL di bawah ke kolom "QR Code Image Url", lalu klik "Scan QR"</span>
                        </li>
                        <li class="flex items-start gap-2 text-xs text-amber-800">
                            <span class="shrink-0 w-4 h-4 rounded-full bg-amber-200 text-amber-800 text-[10px] font-bold flex items-center justify-center mt-0.5">3</span>
                            <span>Pilih <strong>"Pay / Bayar"</strong> untuk simulasi pembayaran berhasil</span>
                        </li>
                    </ol>
                </div>

                <div class="flex gap-2">
                    <div class="relative flex-1 min-w-0">
                        <input
                            type="text"
                            readonly
                            value="{{ $simulatorQrCodeUrl }}"
                            id="pay-qris-simulator-url"
                            class="w-full rounded-xl border border-amber-200 bg-white/80 px-3 py-2.5 text-xs font-mono text-amber-900 pr-2 truncate">
                    </div>
                    <button
                        type="button"
                        id="pay-qris-copy-url"
                        class="copy-btn shrink-0 rounded-xl bg-amber-400 hover:bg-amber-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-amber-200 flex items-center gap-1.5">
                        <svg id="copy-icon" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span id="copy-label">Copy</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- Success Overlay (hidden initially) --}}
    <div id="success-overlay" class="fixed inset-0 flex items-center justify-center z-50 hidden" style="background: rgba(239,246,255,0.95); backdrop-filter: blur(12px);">
        <div class="text-center px-8">
            <div class="relative w-24 h-24 mx-auto mb-6">
                <svg viewBox="0 0 96 96" class="w-24 h-24">
                    <circle cx="48" cy="48" r="44" fill="#eff6ff" stroke="#3b82f6" stroke-width="3" />
                    <circle cx="48" cy="48" r="44" fill="none" stroke="#dbeafe" stroke-width="8" stroke-dasharray="276" stroke-dashoffset="0" class="opacity-40" />
                    <path class="check-path" d="M28 50 L42 64 L68 36" fill="none" stroke="#3b82f6" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="absolute inset-0 rounded-full bg-blue-400/10 pulse-ring"></div>
            </div>
            <h2 class="text-2xl font-semibold text-blue-900 mb-2 tracking-tight">Pembayaran Berhasil!</h2>
            <p class="text-blue-400 text-sm mb-6 font-light">Terima kasih, transaksi Anda telah dikonfirmasi</p>
            <div class="inline-flex items-center gap-2 text-blue-500 text-sm">
                <svg class="spinner w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12" />
                </svg>
                Mengalihkan halaman...
            </div>
        </div>
    </div>

    {{-- Config --}}
    <script type="application/json" id="pay-qris-config">
        @json(['paymentStatusUrl' => $paymentStatusUrl, 'continueUrl' => $continueUrl])
    </script>

    <script>
        (function() {
            const config = JSON.parse(document.getElementById('pay-qris-config').textContent);
            const paymentStatusUrl = config.paymentStatusUrl;
            const continueUrl = config.continueUrl;
            const POLL_INTERVAL_MS = 3000;
            let pollTimer = null;

            function stopPolling() {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
            }

            function showSuccess() {
                stopPolling();
                const overlay = document.getElementById('success-overlay');
                if (overlay) overlay.classList.remove('hidden');
                document.getElementById('pay-qris-status').textContent = 'Pembayaran berhasil!';
            }

            function checkStatus() {
                fetch(paymentStatusUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'paid' && data.redirect_url) {
                            showSuccess();
                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 1800);
                        }
                    })
                    .catch(() => {});
            }

            if (continueUrl && paymentStatusUrl) {
                pollTimer = setInterval(checkStatus, POLL_INTERVAL_MS);
                checkStatus();
            }

            // QR image error
            document.getElementById('pay-qris-img')?.addEventListener('error', function() {
                const wrap = document.getElementById('pay-qris-qr-wrap');
                if (wrap) {
                    wrap.innerHTML = `
                    <div class="w-72 h-72 flex flex-col items-center justify-center gap-3 bg-red-50 rounded-3xl border-2 border-dashed border-red-200">
                        <svg class="w-10 h-10 text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm text-red-400 font-medium text-center px-4">Gagal memuat QR.<br>Silakan refresh halaman.</p>
                    </div>`;
                }
            });

            // Copy button
            const copyBtn = document.getElementById('pay-qris-copy-url');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    const input = document.getElementById('pay-qris-simulator-url');
                    if (!input) return;
                    input.select();
                    input.setSelectionRange(0, 99999);
                    navigator.clipboard.writeText(input.value).then(() => {
                        const label = document.getElementById('copy-label');
                        const icon = document.getElementById('copy-icon');
                        if (label) label.textContent = 'Copied!';
                        if (icon) icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>`;
                        copyBtn.classList.add('bg-green-400', 'hover:bg-green-500');
                        copyBtn.classList.remove('bg-amber-400', 'hover:bg-amber-500');
                        setTimeout(() => {
                            if (label) label.textContent = 'Copy';
                            if (icon) icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>`;
                            copyBtn.classList.remove('bg-green-400', 'hover:bg-green-500');
                            copyBtn.classList.add('bg-amber-400', 'hover:bg-amber-500');
                        }, 2000);
                    });
                });
            }
        })();
    </script>
</body>

</html>