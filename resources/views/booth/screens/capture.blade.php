<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
    'backId' => 'btn-capture-back',
    'centerId' => 'capture-status',
    'centerLabel' => '0 / 1 foto',
    ])

    <div class="kiosk-screen-body flex min-h-0 flex-1 flex-col overflow-y-auto p-5">
        <div class="mx-auto grid w-full max-w-4xl flex-1 grid-cols-1 gap-5 md:grid-cols-3 md:items-start">

            {{-- Kiri: kamera + exposure --}}
            <div class="flex flex-col gap-3 md:col-span-2">
                <div id="camera-wrap"
                    class="relative w-full overflow-hidden bg-black"
                    style="aspect-ratio:4/3; border-radius:var(--radius);">

                    <video id="video-preview"
                        class="-scale-x-100 absolute inset-0 h-full w-full object-cover"
                        autoplay playsinline muted></video>

                    {{-- Slot guides --}}
                    <div id="capture-slot-overlay" class="pointer-events-none absolute inset-0 hidden">
                        <div id="slot-top" class="absolute left-0 right-0 top-0" style="background:rgba(0,0,0,0.35);"></div>
                        <div id="slot-left" class="absolute bottom-0 left-0 top-0" style="background:rgba(0,0,0,0.35);"></div>
                        <div id="slot-right" class="absolute bottom-0 right-0 top-0" style="background:rgba(0,0,0,0.35);"></div>
                        <div id="slot-bottom" class="absolute bottom-0 left-0 right-0" style="background:rgba(0,0,0,0.35);"></div>
                    </div>

                    {{-- Countdown --}}
                    <div id="countdown-overlay"
                        class="absolute inset-0 hidden items-center justify-center"
                        style="background:rgba(0,0,0,0.5);">
                        <span id="countdown-number"
                            class="font-bold drop-shadow-lg"
                            style="font-size:6rem; color:#fff;"
                            aria-live="polite">3</span>
                    </div>

                    {{-- Start camera overlay --}}
                    <div id="camera-start-overlay"
                        class="absolute inset-0 flex items-center justify-center"
                        style="background:rgba(0,0,0,0.6);">
                        <button type="button" id="btn-start-camera"
                            class="kiosk-btn-primary"
                            style="font-size:1rem; padding:0.875rem 2rem; border-radius:var(--radius);">
                            Mulai Kamera
                        </button>
                    </div>

                    {{-- Start capture overlay --}}
                    <div id="capture-start-overlay"
                        class="absolute inset-0 hidden items-center justify-center">
                        <button type="button" id="btn-start-capture"
                            class="kiosk-btn-primary"
                            style="font-size:1rem; padding:0.875rem 2rem; border-radius:var(--radius);">
                            Mulai
                        </button>
                    </div>
                </div>

                {{-- Exposure --}}
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <label for="capture-exposure"
                            class="text-xs font-medium tracking-wide uppercase"
                            style="color:var(--text-muted); letter-spacing:.06em;">
                            Kecerahan
                        </label>
                        <span id="exposure-value" class="text-xs" style="color:var(--text-dim);">50</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="h-3.5 w-3.5 shrink-0" style="color:var(--text-dim);" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" />
                        </svg>
                        <input type="range" id="capture-exposure"
                            min="0" max="100" value="50"
                            class="flex-1 cursor-pointer"
                            style="accent-color: var(--primary);"
                            aria-label="Kecerahan" />
                        <svg class="h-4 w-4 shrink-0" style="color:var(--text-muted);" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Kanan: foto + tombol lanjut --}}
            <div class="flex flex-col gap-4 md:h-full md:justify-between">
                <div class="flex flex-col gap-2">
                    <p class="text-xs font-semibold uppercase tracking-widest"
                        style="color:var(--text-muted); letter-spacing:.08em;">Hasil Foto</p>
                    <p class="text-xs" style="color:var(--text-dim);">Klik foto untuk ambil ulang</p>
                    <div id="capture-photos" class="mt-1 space-y-2.5">
                        <div id="capture-empty"
                            class="flex flex-col items-center justify-center gap-2 rounded-xl py-10 text-center"
                            style="background:var(--bg-card); border:1px solid var(--border);">
                            <svg class="h-6 w-6" style="color:var(--text-dim);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-xs" style="color:var(--text-dim);">Belum ada foto</span>
                        </div>
                    </div>
                </div>

                <button type="button" id="btn-capture-next" disabled
                    class="kiosk-btn-primary w-full py-3.5 text-sm"
                    style="border-radius:var(--radius-sm);">
                    Selanjutnya
                </button>
            </div>
        </div>
    </div>
    <canvas id="capture-canvas" class="hidden" aria-hidden="true"></canvas>
</div>