<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
    'backId' => 'btn-frame-back',
    'title' => 'Pilih Bingkai',
    'subtitle' => 'Pilih desain yang kamu suka',
    'primaryId' => 'btn-frame-next',
    'primaryLabel' => 'Lanjut',
    'primaryDisabled' => true,
    ])

    <div class="kiosk-screen-body flex-1 overflow-y-auto p-5">
        <div id="frame-grid"
            class="mx-auto grid w-full max-w-4xl grid-cols-3 gap-4 animate-fade-up">
            @forelse ($frames as $frame)
            <button
                type="button"
                class="frame-card kiosk-frame-card flex flex-col items-stretch text-left"
                data-frame-id="{{ $frame->id }}">
                <span class="kiosk-frame-card__image-wrapper mb-2.5 flex w-full items-center justify-center overflow-hidden"
                    style="aspect-ratio:2/3;">
                    <img
                        src="{{ (str_starts_with($frame->preview_image ?? '', 'http')) ? $frame->preview_image : asset('storage/'.$frame->preview_image) }}"
                        alt="{{ $frame->name }}"
                        class="h-full w-full object-contain"
                        loading="lazy" />
                </span>
                <span class="kiosk-frame-card__label line-clamp-2 text-center">
                    {{ $frame->name }}
                </span>
            </button>
            @empty
            <div class="col-span-full flex flex-col items-center justify-center py-20 text-center gap-3">
                <svg class="h-10 w-10" style="color:var(--text-dim);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p style="color:var(--text-dim); font-size:0.875rem;">Belum ada bingkai tersedia.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>