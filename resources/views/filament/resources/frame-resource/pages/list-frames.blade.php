<x-filament-panels::page>
    {{ $this->table }}

    @if ($showCreateModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background: rgba(0,0,0,0.4); backdrop-filter: blur(8px);"
        wire:click.self="closeModal">

        <div
            class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-950 p-4"
            @click.stop>

            {{-- Header --}}
            <div class="flex items-center justify-between px-8 pt-8 pb-0">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Bingkai Baru</h2>
                    <p class="mt-0.5 text-sm text-gray-400">Isi detail untuk membuat bingkai</p>
                </div>
                <button
                    type="button"
                    wire:click="closeModal"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition-colors dark:bg-gray-800 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="space-y-6 px-8 py-6">

                @if ($modalValidationError)
                <div class="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    {{ $modalValidationError }}
                </div>
                @endif

                {{-- Nama --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        Nama Bingkai
                    </label>
                    <input
                        type="text"
                        wire:model="modalFrameName"
                        placeholder="mis. Frame Wedding 2024"
                        class="block w-full rounded-xl border-0 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-300 ring-1 ring-gray-200 transition
                               focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900
                               dark:bg-gray-900 dark:text-white dark:placeholder-gray-600 dark:ring-gray-800 dark:focus:ring-white" />
                </div>

                {{-- Ukuran --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        Ukuran Frame
                    </label>
                    <div class="flex gap-2">
                        @foreach (['1200x1800' => '1200×1800', '1200x2400' => '1200×2400', '1200x3600' => '1200×3600'] as $val => $label)
                        <button
                            type="button"
                            wire:click="selectSize('{{ $val }}')"
                            class="flex-1 rounded-xl py-2.5 text-xs font-semibold transition-all
                                {{ $modalFrameSize === $val
                                    ? 'bg-gray-900 text-white shadow-sm dark:bg-white dark:text-gray-900'
                                    : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Upload --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        Frame Overlay
                    </label>
                    <label class="group relative flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 py-8 transition-all hover:border-gray-400 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-600">
                        <input type="file" wire:model="modalFrameOverlay" accept="image/png" class="sr-only" />

                        @if ($modalFrameOverlay)
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-500/10">
                            <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">File siap diunggah</span>
                        <span class="text-xs text-gray-400">Klik untuk ganti</span>
                        @else
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-800">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Unggah file PNG</span>
                        <span class="text-xs text-gray-300 dark:text-gray-600">PNG transparan · maks 10MB</span>
                        @endif
                    </label>

                    <div wire:loading wire:target="modalFrameOverlay" class="flex items-center gap-2 px-1 text-xs text-gray-400">
                        <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Mengunggah...
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center gap-3 px-8 pb-8">
                <button
                    type="button"
                    wire:click="closeModal"
                    class="flex-1 rounded-xl border border-gray-200 py-3 text-sm font-medium text-gray-500 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-900">
                    Batal
                </button>

                <button
                    type="button"
                    wire:click="submitAndGoToLayout"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-wait"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-black py-3 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 disabled:opacity-60">
                    <svg wire:loading.remove wire:target="submitAndGoToLayout" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7" />
                    </svg>
                    <svg wire:loading wire:target="submitAndGoToLayout" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Atur Posisi Foto
                </button>
            </div>

        </div>
    </div>
    @endif

</x-filament-panels::page>