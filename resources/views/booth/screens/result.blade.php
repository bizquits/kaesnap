{{-- NOTE: Save to result.blade.php --}}
<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col" data-state="RESULT">
    @include('booth.components.screen-header', ['title' => 'MISSION COMPLETE'])

    <div class="kiosk-screen-body flex-1 overflow-y-auto p-5">
        <div class="mx-auto flex max-w-md flex-col items-center gap-6 text-center animate-fade-up">

            {{-- Upload status --}}
            <div id="result-upload-status" class="w-full">
                <div class="flex flex-col items-center gap-3 py-8">
                    <div class="arcade-loading" style="justify-content:center;">
                        <span></span><span></span><span></span>
                    </div>
                    <p style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:0.75rem;
                               letter-spacing:0.15em;text-transform:uppercase;color:var(--text-muted);">
                        UPLOADING...
                    </p>
                </div>
            </div>

            {{-- QR section --}}
            <div id="result-qr-section" class="hidden w-full flex-col items-center gap-5">

                {{-- Timer bar --}}
                <div id="result-timer-wrap" class="w-full flex flex-col items-center gap-2">
                    <p style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:0.7rem;
                               letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);">
                        SESSION ENDS IN
                        <span id="result-timer-count"
                            style="font-family:'Orbitron',monospace;color:var(--cyan);
                                   text-shadow:0 0 8px var(--cyan-glow);">60</span>s
                    </p>
                    <div class="arcade-progress-bar" style="--progress:100%;">
                        <div id="result-timer-bar"></div>
                    </div>
                </div>

                <p style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:0.85rem;
                           letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">
                    SCAN QR TO DOWNLOAD
                </p>

                <div id="result-qr-code" style="display:inline-block;"></div>

                <div style="font-family:'Rajdhani',sans-serif;font-size:0.75rem;
                             letter-spacing:0.06em;color:var(--text-dim);">
                    <p style="text-transform:uppercase;margin:0 0 0.25rem;">OR VISIT</p>
                    <p id="result-url"
                        style="font-family:'Orbitron',monospace;font-size:0.65rem;
                               color:var(--blue);text-shadow:0 0 6px var(--blue-glow);
                               word-break:break-all;margin:0;"></p>
                </div>
            </div>

            {{-- Error --}}
            <div id="result-error" class="hidden w-full px-4 py-3"
                style="background:rgba(255,62,108,0.08);border:1px solid rgba(255,62,108,0.25);
                       border-radius:var(--radius-sm);color:var(--danger);
                       font-family:'Rajdhani',sans-serif;font-weight:700;
                       font-size:0.8rem;letter-spacing:0.08em;text-transform:uppercase;">
                UPLOAD FAILED. TRY AGAIN.
            </div>

            {{-- Actions --}}
            <div id="result-actions" class="hidden w-full">
                <button type="button" id="btn-result-home"
                    class="w-full py-3.5"
                    style="background:var(--bg-card);border:1px solid var(--border-md);
                           border-radius:var(--radius-sm);color:var(--text-muted);
                           font-family:'Rajdhani',sans-serif;font-weight:700;font-size:0.8rem;
                           letter-spacing:0.12em;text-transform:uppercase;cursor:pointer;
                           transition:all 0.15s ease;">
                    ← BACK TO HOME
                </button>
            </div>
        </div>
    </div>
</div>