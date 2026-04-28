{{-- Modal: Buat Bingkai Baru --}}
@if ($showCreateModal)
<div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
    wire:click.self="closeModal">
    <div
        class="relative w-full max-w-lg mx-4 rounded-2xl bg-white shadow-2xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10"
        @click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                Buat Bingkai Baru
            </h2>
            <button
                type="button"
                wire:click="closeModal"
                class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="space-y-5 px-6 py-5">

            {{-- Pesan error --}}
            @if ($modalValidationError)
            <div class="rounded-lg bg-danger-50 px-4 py-3 text-sm text-danger-700 dark:bg-danger-400/10 dark:text-danger-400">
                {{ $modalValidationError }}
            </div>
            @endif

            {{-- Nama bingkai --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Bingkai <span class="text-danger-600">*</span>
                </label>
                <input
                    type="text"
                    wire:model="modalFrameName"
                    placeholder="Contoh: Frame Wedding 2024"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                           focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500
                           dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500" />
            </div>

            {{-- Ukuran frame --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Ukuran Frame <span class="text-danger-600">*</span>
                </label>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Lebar × Tinggi dalam piksel (resolusi cetak)
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach (['1200x1800' => '1200 × 1800', '1200x2400' => '1200 × 2400', '1200x3600' => '1200 × 3600'] as $val => $label)
                    <button
                        type="button"
                        wire:click="selectSize('{{ $val }}')"
                        class="rounded-lg border-2 px-4 py-2 text-sm font-semibold transition-colors
                                {{ $modalFrameSize === $val
                                    ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300'
                                    : 'border-gray-300 bg-white text-gray-700 hover:border-primary-400 hover:bg-primary-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Upload overlay --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Frame Overlay (PNG transparan) <span class="text-danger-600">*</span>
                </label>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    File PNG dengan area foto yang sudah transparan.
                </p>

                <label
                    class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6
                           hover:border-primary-400 hover:bg-primary-50 dark:border-gray-600 dark:bg-gray-800 dark:hover:border-primary-500">
                    <input
                        type="file"
                        wire:model="modalFrameOverlay"
                        accept="image/png"
                        class="sr-only" />
                    @if ($modalFrameOverlay)
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-8 w-8 text-success-500" />
                    <span class="mt-2 text-sm font-medium text-success-600 dark:text-success-400">
                        File dipilih: {{ is_object($modalFrameOverlay) ? $modalFrameOverlay->getClientOriginalName() : 'overlay.png' }}
                    </span>
                    <span class="mt-1 text-xs text-gray-400">Klik untuk ganti file</span>
                    @else
                    <x-filament::icon icon="heroicon-o-arrow-up-tray" class="h-8 w-8 text-gray-400" />
                    <span class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                        Klik untuk unggah file PNG
                    </span>
                    <span class="mt-1 text-xs text-gray-400">PNG, maks 10MB</span>
                    @endif
                </label>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
            <button
                type="button"
                wire:click="closeModal"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700
                       hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                Batal
            </button>

            <button
                type="button"
                wire:click="submitAndGoToLayout"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-70 cursor-wait"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2 text-sm font-semibold text-white
                       shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500
                       disabled:opacity-70">
                <span wire:loading.remove wire:target="submitAndGoToLayout">
                    <x-filament::icon icon="heroicon-o-adjustments-horizontal" class="h-4 w-4" />
                </span>
                <span wire:loading wire:target="submitAndGoToLayout">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                </span>
                Atur Posisi Foto
            </button>
        </div>
    </div>
</div>
@endif