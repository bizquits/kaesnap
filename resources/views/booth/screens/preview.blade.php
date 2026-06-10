{{-- NOTE: Save this block to its own file: preview.blade.php --}}
<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
    'backId' => 'btn-preview-back',
    'title' => 'PREVIEW',
    'subtitle' => 'Final output',
    ])

    <div class="kiosk-screen-body flex-1 overflow-y-auto p-5">
        <div class="mx-auto flex w-full max-w-lg flex-col items-center gap-5">

            {{-- Merged preview --}}
            <div id="preview-merged"
                class="flex w-full items-center justify-center overflow-hidden animate-fade-up"
                style="border-radius:var(--radius);
                       background:var(--bg-card);
                       border:1px solid var(--border-md);
                       min-height:260px;
                       box-shadow:0 0 24px var(--blue-dim),0 0 60px rgba(5,100,246,0.08);">
                <div style="text-align:center;padding:2rem;">
                    <div class="arcade-loading" style="justify-content:center;margin-bottom:0.75rem;">
                        <span></span><span></span><span></span>
                    </div>
                    <p style="font-family:'Press Start 2P',monospace;font-size:0.45rem;
                               color:var(--text-dim);letter-spacing:0.1em;">RENDERING...</p>
                </div>
            </div>

            <button type="button" id="btn-preview-print" disabled
                class="kiosk-btn-primary w-full"
                style="padding:1rem;font-size:0.85rem;">
                PRINT ▶
            </button>
        </div>
    </div>
</div>