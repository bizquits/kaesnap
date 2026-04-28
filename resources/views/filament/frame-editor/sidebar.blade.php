<div class="w-72 shrink-0 space-y-4 xl:w-80">

    {{-- ── INFO BINGKAI ────────────────────────────────────────────────── --}}
    <x-filament::section heading="Informasi Bingkai" class="shadow-sm">
        <div class="space-y-3">

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                    Nama Bingkai
                </label>
                <input
                    type="text"
                    wire:model="name"
                    class="mt-1 block w-full rounded-lg border-gray-300 py-2 text-sm shadow-sm
                           focus:border-primary-500 focus:ring-primary-500
                           dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                    Ukuran Canvas
                </label>
                <div class="mt-1 rounded-lg bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $canvasWidth }} × {{ $canvasHeight }} px
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                    Ukuran ditentukan saat buat baru dan tidak bisa diubah di sini.
                </p>
            </div>

        </div>
    </x-filament::section>

    {{-- ── LAYERING FOTO ──────────────────────────────────────────────── --}}
    <x-filament::section heading="Posisi Foto terhadap Overlay" class="shadow-sm">
        <div class="space-y-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Tentukan apakah slot foto berada <strong>di belakang</strong> atau
                <strong>di depan</strong> gambar frame overlay.
            </p>

            <div class="grid grid-cols-2 gap-2">
                {{-- Tombol: Di Belakang --}}
                <button
                    type="button"
                    wire:click="$set('photoLayer', 'behind')"
                    class="flex flex-col items-center gap-1 rounded-xl border-2 px-3 py-3 text-xs font-semibold transition-all
                        {{ $photoLayer === 'behind'
                            ? 'border-primary-500 bg-primary-50 text-primary-700 shadow-sm dark:bg-primary-500/10 dark:text-primary-300'
                            : 'border-gray-300 bg-white text-gray-600 hover:border-primary-400 hover:bg-primary-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{-- Ikon: foto di belakang overlay (kotak abu di bawah, overlay di atas) --}}
                    <svg viewBox="0 0 40 40" class="h-8 w-8" fill="none">
                        {{-- Foto slot --}}
                        <rect x="4" y="8" width="32" height="24" rx="2" fill="{{ $photoLayer === 'behind' ? '#6366f1' : '#9ca3af' }}" opacity="0.4" />
                        {{-- Overlay frame (di depan) --}}
                        <rect x="4" y="8" width="32" height="24" rx="2" stroke="{{ $photoLayer === 'behind' ? '#6366f1' : '#6b7280' }}" stroke-width="2.5" fill="none" />
                        {{-- Tanda "overlay di depan" --}}
                        <path d="M4 8 L36 32 M36 8 L4 32" stroke="{{ $photoLayer === 'behind' ? '#6366f1' : '#d1d5db' }}" stroke-width="1.5" stroke-dasharray="3 2" opacity="0.7" />
                    </svg>
                    Di Belakang Overlay
                </button>

                {{-- Tombol: Di Depan --}}
                <button
                    type="button"
                    wire:click="$set('photoLayer', 'front')"
                    class="flex flex-col items-center gap-1 rounded-xl border-2 px-3 py-3 text-xs font-semibold transition-all
                        {{ $photoLayer === 'front'
                            ? 'border-primary-500 bg-primary-50 text-primary-700 shadow-sm dark:bg-primary-500/10 dark:text-primary-300'
                            : 'border-gray-300 bg-white text-gray-600 hover:border-primary-400 hover:bg-primary-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{-- Ikon: overlay di belakang, foto slot di depan --}}
                    <svg viewBox="0 0 40 40" class="h-8 w-8" fill="none">
                        {{-- Overlay frame (di belakang) --}}
                        <rect x="4" y="8" width="32" height="24" rx="2" stroke="{{ $photoLayer === 'front' ? '#6366f1' : '#6b7280' }}" stroke-width="2.5" fill="none" stroke-dasharray="4 2" opacity="0.6" />
                        {{-- Foto slot (di depan) --}}
                        <rect x="8" y="12" width="24" height="18" rx="2" fill="{{ $photoLayer === 'front' ? '#6366f1' : '#9ca3af' }}" opacity="0.5" />
                        <rect x="8" y="12" width="24" height="18" rx="2" stroke="{{ $photoLayer === 'front' ? '#6366f1' : '#6b7280' }}" stroke-width="1.5" fill="none" />
                    </svg>
                    Di Depan Overlay
                </button>
            </div>

            <div class="rounded-lg {{ $photoLayer === 'behind' ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-amber-50 dark:bg-amber-900/20' }} px-3 py-2 text-xs">
                @if ($photoLayer === 'behind')
                <span class="text-blue-700 dark:text-blue-300">
                    ✦ Foto akan muncul <strong>di bawah</strong> frame overlay.
                    Cocok jika overlay memiliki border/dekorasi yang menutupi tepi foto.
                </span>
                @else
                <span class="text-amber-700 dark:text-amber-300">
                    ✦ Foto akan muncul <strong>di atas</strong> frame overlay.
                    Cocok jika overlay hanya sebagai background/watermark.
                </span>
                @endif
            </div>
        </div>
    </x-filament::section>

    {{-- ── PHOTO SLOTS ─────────────────────────────────────────────────── --}}
    <x-filament::section heading="Slot Foto" class="shadow-sm">
        <div class="space-y-2">
            <x-filament::button
                color="primary"
                icon="heroicon-o-plus"
                wire:click="addSlot"
                class="w-full">
                Tambah Slot Foto
            </x-filament::button>

            @if (empty($slots))
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Belum ada slot foto. Klik <strong>Tambah Slot Foto</strong> untuk mulai.
            </p>
            @else
            <ul class="max-h-72 space-y-1 overflow-auto pr-1">
                @foreach ($slots as $index => $slot)
                @php $isActive = $selectedSlotId === $slot['id']; @endphp
                <li class="flex items-center justify-between rounded-lg px-2 py-1.5 text-sm
                            {{ $isActive
                                ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                : 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
                    <button
                        type="button"
                        wire:click="selectSlot({{ $slot['id'] }})"
                        class="flex flex-1 items-center justify-between gap-2 text-left">
                        <span class="font-medium">Slot {{ $index + 1 }}</span>
                        <span class="text-xs text-gray-400">
                            {{ $slot['width'] }}×{{ $slot['height'] }}
                        </span>
                    </button>

                    <button
                        type="button"
                        wire:click="deleteSlot({{ $slot['id'] }})"
                        class="ml-1 rounded p-1 text-gray-400
                                       hover:bg-red-50 hover:text-red-600
                                       dark:hover:bg-red-900/20 dark:hover:text-red-400"
                        title="Hapus slot">
                        <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                    </button>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </x-filament::section>

    {{-- ── SIMPAN ──────────────────────────────────────────────────────── --}}
    <x-filament::button
        color="primary"
        icon="heroicon-o-check"
        wire:click="saveSlots"
        class="w-full"
        size="lg">
        Simpan Layout
    </x-filament::button>

</div>