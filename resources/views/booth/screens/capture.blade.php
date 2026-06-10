<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
    'backId' => 'btn-capture-back',
    'centerId' => 'capture-status',
    'centerLabel' => '0 / 1 SHOT',
    ])

    <div class="kiosk-screen-body flex min-h-0 flex-1 flex-col overflow-y-auto p-4">
        <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-4 md:flex-row md:items-start">

            {{-- Kamera (full/main) --}}
            <div class="flex flex-col gap-3 md:flex-1">

                {{-- Camera viewport --}}
                <div id="camera-wrap"
                    class="relative w-full overflow-hidden"
                    style="aspect-ratio:4/3; border-radius:var(--radius); background:#000;
                           border:1px solid var(--border-md);
                           box-shadow:0 0 0 1px var(--border),0 0 30px var(--blue-dim),0 0 60px rgba(5,100,246,0.08);">

                    <video id="video-preview"
                        class="-scale-x-100 absolute inset-0 h-full w-full object-cover"
                        autoplay playsinline muted></video>

                    {{-- HUD corner overlays --}}
                    <div class="pointer-events-none absolute inset-0" style="z-index:2;">
                        {{-- Top-left corner --}}
                        <div style="position:absolute;top:10px;left:10px;width:24px;height:24px;
                                    border-top:2px solid var(--cyan);border-left:2px solid var(--cyan);
                                    box-shadow:0 0 8px var(--cyan-glow);"></div>
                        {{-- Top-right corner --}}
                        <div style="position:absolute;top:10px;right:10px;width:24px;height:24px;
                                    border-top:2px solid var(--cyan);border-right:2px solid var(--cyan);
                                    box-shadow:0 0 8px var(--cyan-glow);"></div>
                        {{-- Bottom-left corner --}}
                        <div style="position:absolute;bottom:10px;left:10px;width:24px;height:24px;
                                    border-bottom:2px solid var(--cyan);border-left:2px solid var(--cyan);
                                    box-shadow:0 0 8px var(--cyan-glow);"></div>
                        {{-- Bottom-right corner --}}
                        <div style="position:absolute;bottom:10px;right:10px;width:24px;height:24px;
                                    border-bottom:2px solid var(--cyan);border-right:2px solid var(--cyan);
                                    box-shadow:0 0 8px var(--cyan-glow);"></div>
                        {{-- Center crosshair --}}
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                                    width:20px;height:20px;opacity:0.3;">
                            <div style="position:absolute;top:50%;left:0;right:0;height:1px;background:var(--cyan);"></div>
                            <div style="position:absolute;left:50%;top:0;bottom:0;width:1px;background:var(--cyan);"></div>
                        </div>
                    </div>

                    {{-- Slot guides --}}
                    <div id="capture-slot-overlay" class="pointer-events-none absolute inset-0 hidden" style="z-index:3;">
                        <div id="slot-top" class="absolute left-0 right-0 top-0" style="background:rgba(3,7,20,0.55);"></div>
                        <div id="slot-left" class="absolute bottom-0 left-0 top-0" style="background:rgba(3,7,20,0.55);"></div>
                        <div id="slot-right" class="absolute bottom-0 right-0 top-0" style="background:rgba(3,7,20,0.55);"></div>
                        <div id="slot-bottom" class="absolute bottom-0 left-0 right-0" style="background:rgba(3,7,20,0.55);"></div>
                    </div>

                    {{-- Countdown overlay --}}
                    <div id="countdown-overlay"
                        class="absolute inset-0 hidden items-center justify-center"
                        style="z-index:5;">
                        <div style="text-align:center;">
                            <span id="countdown-number"
                                aria-live="polite"
                                style="font-family:'Orbitron',monospace;font-weight:900;font-size:7rem;
                                       color:var(--cyan);text-shadow:0 0 20px var(--cyan-glow),0 0 60px rgba(0,245,255,0.3);
                                       display:block;line-height:1;">3</span>
                            <p style="font-family:'Press Start 2P',monospace;font-size:0.5rem;
                                      color:var(--text-muted);letter-spacing:0.1em;margin-top:0.5rem;">
                                SMILE!
                            </p>
                        </div>
                    </div>

                    {{-- Start camera overlay --}}
                    <div id="camera-start-overlay"
                        class="absolute inset-0 flex items-center justify-center"
                        style="background:rgba(3,7,20,0.75);backdrop-filter:blur(4px);z-index:6;">
                        <div style="text-align:center;">
                            <div style="font-family:'Press Start 2P',monospace;font-size:0.55rem;
                                        color:var(--text-dim);letter-spacing:0.1em;margin-bottom:1.5rem;">
                                CAMERA OFFLINE
                            </div>
                            <button type="button" id="btn-start-camera" class="kiosk-btn-primary"
                                style="font-size:0.8rem;padding:0.875rem 2rem;">
                                INITIALIZE
                            </button>
                        </div>
                    </div>

                    {{-- Start capture overlay --}}
                    <div id="capture-start-overlay"
                        class="absolute inset-0 hidden items-center justify-center"
                        style="z-index:6;">
                        <button type="button" id="btn-start-capture" class="kiosk-btn-primary"
                            style="font-size:0.85rem;padding:1rem 2.5rem;">
                            ▶ START
                        </button>
                    </div>
                </div>

                {{-- Exposure slider --}}
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);padding:0.75rem 1rem;">
                    <div class="flex items-center justify-between mb-2">
                        <label for="capture-exposure"
                            style="font-family:'Rajdhani',sans-serif;font-size:0.65rem;font-weight:700;
                                   letter-spacing:0.15em;text-transform:uppercase;color:var(--text-dim);">
                            EXPOSURE
                        </label>
                        <span id="exposure-value"
                            style="font-family:'Orbitron',monospace;font-size:0.65rem;font-weight:700;
                                   color:var(--cyan);text-shadow:0 0 6px var(--cyan-glow);">50</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="h-3.5 w-3.5 shrink-0" style="color:var(--text-dim);" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <input type="range" id="capture-exposure"
                            min="0" max="100" value="50"
                            class="flex-1 cursor-pointer"
                            style="accent-color:var(--blue);"
                            aria-label="Exposure" />
                        <svg class="h-4 w-4 shrink-0" style="color:var(--text-muted);" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Kanan: foto + lanjut --}}
            <div class="flex flex-col gap-3 md:w-44 md:h-full md:justify-between">

                {{-- Foto result --}}
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <p style="font-family:'Rajdhani',sans-serif;font-size:0.65rem;font-weight:700;
                                  letter-spacing:0.15em;text-transform:uppercase;color:var(--text-dim);">
                            SHOTS
                        </p>
                        <p style="font-family:'Rajdhani',sans-serif;font-size:0.6rem;font-weight:600;
                                  letter-spacing:0.08em;text-transform:uppercase;color:var(--blue);
                                  text-shadow:0 0 6px var(--blue-glow);">
                            TAP TO RETAKE
                        </p>
                    </div>
                    <div id="capture-photos" class="space-y-2">
                        <div id="capture-empty"
                            class="flex flex-col items-center justify-center gap-2 py-8 text-center"
                            style="background:var(--bg-card);border:1px solid var(--border);
                                   border-radius:var(--radius-sm);">
                            <svg class="h-5 w-5" style="color:var(--text-dim);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span style="font-family:'Press Start 2P',monospace;font-size:0.45rem;
                                         color:var(--text-dim);letter-spacing:0.05em;">NO SHOTS</span>
                        </div>
                    </div>
                </div>

                <button type="button" id="btn-capture-next" disabled
                    class="kiosk-btn-primary w-full"
                    style="padding:0.875rem;font-size:0.8rem;">
                    NEXT ▶
                </button>
            </div>
        </div>
    </div>

    <canvas id="capture-canvas" class="hidden" aria-hidden="true"></canvas>
</div>