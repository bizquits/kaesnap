@php
$frameFile = $record->frame_file
? ((str_starts_with($record->frame_file, 'http'))
? $record->frame_file
: asset('storage/' . $record->frame_file))
: null;

// photoLayer: 'behind' = overlay di atas slot | 'front' = overlay di bawah slot
$layerBehind = ($photoLayer ?? 'behind') === 'behind';
@endphp

<div
    x-data="zoomWorkspace({{ $canvasWidth }}, {{ $canvasHeight }})"
    class="flex h-[600px] flex-col overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 lg:h-[800px]">
    @include('filament.frame-editor.zoom-controls')

    <div x-ref="workspace" class="flex-1 overflow-auto bg-gray-200 p-4 dark:bg-gray-800">
        <div class="flex justify-center pb-6">
            {{-- Wrapper yang ikut zoom --}}
            <div
                :style="{ width: baseWidth * zoom + 'px', height: baseHeight * zoom + 'px' }"
                style="width:{{ $canvasWidth }}px; height:{{ $canvasHeight }}px;">
                <div
                    x-ref="paper"
                    class="relative h-full w-full overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-gray-200">
                    {{-- ── CANVAS AREA ─────────────────────────────────────── --}}
                    <div
                        id="frame-layout-canvas"
                        x-data="frameCanvas({{ $canvasWidth }}, {{ $canvasHeight }})"
                        class="relative h-full w-full">
                        {{-- Overlay di BELAKANG slot (z-0) --}}
                        @if ($frameFile && $layerBehind)
                        <img
                            src="{{ $frameFile }}"
                            class="pointer-events-none absolute inset-0 z-0 h-full w-full object-contain"
                            alt="Frame Overlay" />
                        @endif

                        {{-- Photo Slots (z-10) --}}
                        @foreach ($slots as $index => $slot)
                        @include('filament.frame-editor.slot', ['slot' => $slot, 'index' => $index])
                        @endforeach

                        {{-- Overlay di DEPAN slot (z-20) --}}
                        @if ($frameFile && !$layerBehind)
                        <img
                            src="{{ $frameFile }}"
                            class="pointer-events-none absolute inset-0 z-20 h-full w-full object-contain"
                            alt="Frame Overlay" />
                        @endif
                    </div>
                    {{-- ─────────────────────────────────────────────────────── --}}
                </div>
            </div>
        </div>
    </div>
</div>