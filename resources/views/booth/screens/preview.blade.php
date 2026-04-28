<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
    'backId' => 'btn-preview-back',
    'title' => 'Preview',
    ])

    <div class="kiosk-screen-body flex-1 overflow-y-auto p-5">
        <div class="mx-auto grid w-full max-w-4xl grid-cols-1 gap-6 md:grid-cols-2">

            {{-- Kiri: foto --}}
            <section>
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest"
                    style="color:var(--text-muted); letter-spacing:.08em;">Foto Anda</p>
                <div id="preview-photo-grid" class="grid grid-cols-2 gap-3"></div>
            </section>

            {{-- Kanan: merged preview + lanjut --}}
            <section class="flex flex-col gap-4">
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest"
                        style="color:var(--text-muted); letter-spacing:.08em;">Hasil Akhir</p>
                    <div id="preview-merged"
                        class="flex w-full items-center justify-center overflow-hidden"
                        style="border-radius:var(--radius); background:var(--bg-card); border:1px solid var(--border); min-height:200px; max-height:420px;">
                        <p style="color:var(--text-dim); font-size:0.8125rem;">Pilih foto untuk melihat preview</p>
                    </div>
                </div>

                <button type="button" id="btn-preview-print" disabled
                    class="kiosk-btn-primary w-full py-3.5 text-sm"
                    style="border-radius:var(--radius-sm);">
                    Lanjut ke Print
                </button>
            </section>

        </div>
    </div>
</div>