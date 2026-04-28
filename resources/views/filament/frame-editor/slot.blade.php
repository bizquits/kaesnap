@php
$style = $this->getSlotStyle($slot);
$isActive = $selectedSlotId === $slot['id'];

$cssStyle = sprintf(
'left:%.4f%%;top:%.4f%%;width:%.4f%%;height:%.4f%%;',
$style['left'],
$style['top'],
$style['width'],
$style['height']
);
@endphp

<div
    wire:key="slot-{{ $slot['id'] }}"
    data-slot-id="{{ $slot['id'] }}"
    data-slot-x="{{ $slot['x'] }}"
    data-slot-y="{{ $slot['y'] }}"
    data-slot-w="{{ $slot['width'] }}"
    data-slot-h="{{ $slot['height'] }}"
    class="absolute z-10 cursor-move select-none rounded border-2
        {{ $isActive
            ? 'border-primary-500 bg-primary-100/50 dark:bg-primary-500/20'
            : 'border-gray-500 bg-gray-200/60 dark:bg-gray-700/50' }}"
    style="{{ $cssStyle }}"
    @mousedown.stop="startDrag($event, {{ $slot['id'] }}, {{ $slot['x'] }}, {{ $slot['y'] }}, {{ $slot['width'] }}, {{ $slot['height'] }})"
    wire:click.stop="selectSlot({{ $slot['id'] }})">
    {{-- Label --}}
    <div class="pointer-events-none flex h-full w-full flex-col items-center justify-center text-center">
        <div class="text-sm font-bold
            {{ $isActive ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300' }}">
            Slot {{ $index + 1 }}
        </div>
        <div class="text-2xs
            {{ $isActive ? 'text-primary-500 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">
            {{ $slot['width'] }} × {{ $slot['height'] }} px
        </div>
    </div>

    {{-- 8 Resize Handles (hanya ketika slot aktif) --}}
    @if ($isActive)
    @php
    $handles = [
    'n' => 'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 cursor-n-resize',
    's' => 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 cursor-s-resize',
    'e' => 'top-1/2 right-0 translate-x-1/2 -translate-y-1/2 cursor-e-resize',
    'w' => 'top-1/2 left-0 -translate-x-1/2 -translate-y-1/2 cursor-w-resize',
    'nw' => 'top-0 left-0 -translate-x-1/2 -translate-y-1/2 cursor-nw-resize',
    'ne' => 'top-0 right-0 translate-x-1/2 -translate-y-1/2 cursor-ne-resize',
    'sw' => 'bottom-0 left-0 -translate-x-1/2 translate-y-1/2 cursor-sw-resize',
    'se' => 'bottom-0 right-0 translate-x-1/2 translate-y-1/2 cursor-se-resize',
    ];
    @endphp

    @foreach ($handles as $dir => $posClasses)
    <div
        class="absolute {{ $posClasses }} z-20 h-3.5 w-3.5 rounded-full border-2 border-primary-500 bg-white shadow-md"
        @mousedown.stop="startResize($event, {{ $slot['id'] }}, '{{ $dir }}', {{ $slot['x'] }}, {{ $slot['y'] }}, {{ $slot['width'] }}, {{ $slot['height'] }})"></div>
    @endforeach
    @endif
</div>