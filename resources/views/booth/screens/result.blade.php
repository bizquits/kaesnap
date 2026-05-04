<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col" data-state="RESULT">
    @include('booth.components.screen-header', ['title' => 'Foto Siap!'])

    <div class="kiosk-screen-body flex-1 overflow-y-auto p-5">
        <div class="mx-auto flex max-w-md flex-col items-center gap-6 text-center animate-fade-up">

            {{-- Upload status --}}
            <div id="result-upload-status" class="w-full">
                <div class="flex flex-col items-center gap-3">
                    <svg class="h-6 w-6 animate-spin" style="color:var(--text-muted);" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <p class="text-sm" style="color:var(--text-muted);">Mengupload foto...</p>
                </div>
            </div>

            {{-- QR section --}}
            <div id="result-qr-section" class="hidden w-full flex-col items-center gap-4">

                {{-- Timer bar --}}
                <div id="result-timer-wrap" class="w-full flex flex-col items-center gap-2 mb-10">
                    <p class="text-xs" style="color:var(--text-muted);">
                        Sesi berakhir dalam
                        <span id="result-timer-count"
                            class="font-semibold tabular-nums"
                            style="color:var(--text);">60</span>s
                    </p>
                    <div class="w-full overflow-hidden rounded-full" style="height:4px; background:var(--border);">
                        <div id="result-timer-bar"
                            class="h-full rounded-full"
                            style="width:100%; background:var(--primary); transition:width 1s linear; transform-origin:left;"></div>
                    </div>
                </div>

                <p class="text-sm" style="color:var(--text-muted);">Scan QR untuk mengunduh foto Anda</p>

                <div id="result-qr-code"
                    class="rounded-2xl p-5"
                    style="background:#fff; display:inline-block;"></div>

                <div class="text-xs" style="color:var(--text-dim);">
                    <p>Atau kunjungi:</p>
                    <p id="result-url"
                        class="mt-1 break-all font-mono"
                        style="color:var(--text-muted);"></p>
                </div>
            </div>

            {{-- Error --}}
            <div id="result-error"
                class="hidden w-full rounded-xl px-4 py-3 text-sm"
                style="background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2); color:var(--danger);">
                Gagal mengupload foto. Silakan coba lagi.
            </div>

            {{-- Actions --}}
            <div id="result-actions" class="hidden w-full">
                <button type="button" id="btn-result-home"
                    class="w-full py-3.5 text-sm font-medium transition-opacity hover:opacity-70"
                    style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-sm); color:var(--text-muted);">
                    Kembali ke Home
                </button>
            </div>

        </div>
    </div>
</div>