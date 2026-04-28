<div class="kiosk-screen flex h-full w-full flex-col items-center justify-center p-6 gap-6">
    <div class="flex flex-col items-center gap-5 animate-fade-up text-center">

        <div>
            <h1 class="text-2xl font-bold tracking-tight" style="color:var(--text);">Foto Anda Siap</h1>
            <p class="mt-2 text-sm" style="color:var(--text-muted);">Scan QR code untuk mengunduh</p>
        </div>

        <div id="qr-container"
            class="rounded-2xl p-5 shadow-2xl"
            style="background:#fff; display:inline-block;"></div>

        <button type="button" id="btn-reset"
            class="mt-2 py-3.5 px-10 text-sm font-medium transition-opacity hover:opacity-70"
            style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); color:var(--text-muted);">
            Sesi Baru
        </button>
    </div>
</div>