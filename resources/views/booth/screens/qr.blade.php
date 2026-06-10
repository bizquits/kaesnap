{{-- NOTE: Save to qr.blade.php --}}
<div class="kiosk-screen flex h-full w-full flex-col items-center justify-center p-6 gap-6">
    <div class="crt-overlay"></div>
    <div class="flex flex-col items-center gap-6 animate-fade-up text-center">

        {{-- Title --}}
        <div>
            <p style="font-family:'Press Start 2P',monospace;font-size:0.55rem;
                       color:var(--blue);letter-spacing:0.1em;text-transform:uppercase;
                       text-shadow:0 0 10px var(--blue-glow);margin-bottom:0.75rem;">
                ◆ STAGE CLEAR ◆
            </p>
            <h1 style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:2rem;
                        letter-spacing:0.12em;text-transform:uppercase;color:var(--text);
                        text-shadow:0 0 16px var(--blue-glow);margin:0 0 0.25rem;">
                YOUR PHOTO IS READY
            </h1>
            <p style="font-family:'Rajdhani',sans-serif;font-size:0.85rem;font-weight:600;
                       letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);margin:0;">
                Scan QR code to download
            </p>
        </div>

        {{-- QR Code --}}
        <div id="qr-container"></div>

        {{-- New session --}}
        <button type="button" id="btn-reset"
            class="kiosk-btn-primary"
            style="padding:0.875rem 2.5rem;font-size:0.8rem;">
            ▶ NEW GAME
        </button>
    </div>
</div>