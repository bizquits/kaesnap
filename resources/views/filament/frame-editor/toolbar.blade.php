{{-- ── HEADER ───────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between gap-3">
    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
        Editor — {{ $canvasWidth }}×{{ $canvasHeight }} px
    </h3>
</div>

{{-- ── EDITOR BAR (posisi & ukuran slot terpilih) ─────────────────── --}}
<div class="flex h-12 items-center overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 px-3
            dark:border-gray-700 dark:bg-gray-800">
    <div class="flex min-w-max items-center gap-3">
        @if ($selectedSlotId)
        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">
            Slot {{ collect($slots)->firstWhere('id', $selectedSlotId) ? (array_search(collect($slots)->firstWhere('id', $selectedSlotId), $slots) + 1) : '' }}
        </span>

        @foreach (['X' => 'editorX', 'Y' => 'editorY', 'W' => 'editorWidth', 'H' => 'editorHeight'] as $label => $model)
        <div class="flex items-center gap-1">
            <label class="text-[10px] font-bold text-gray-500 dark:text-gray-400">{{ $label }}</label>
            <input
                type="number"
                wire:model.live.debounce.300ms="{{ $model }}"
                class="w-20 rounded border border-gray-300 bg-white px-2 py-1 text-xs
                               dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                min="0"
                max="{{ in_array($model, ['editorWidth', 'editorX']) ? $canvasWidth : $canvasHeight }}" />
        </div>
        @endforeach

        <button
            type="button"
            wire:click="deselectSlot"
            class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600
                       dark:hover:bg-gray-600 dark:hover:text-gray-300"
            title="Batal pilih">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
        @else
        <span class="text-xs text-gray-500 dark:text-gray-400">
            Klik slot untuk memilih · Drag untuk pindahkan · Tarik sudut/tepi untuk ubah ukuran
        </span>
        @endif
    </div>
</div>