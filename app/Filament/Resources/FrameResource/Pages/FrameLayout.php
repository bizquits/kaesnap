<?php

namespace App\Filament\Resources\FrameResource\Pages;

use App\Filament\Resources\FrameResource;
use App\Models\Frame;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Livewire\WithFileUploads;

class FrameLayout extends Page
{
    use WithFileUploads;

    protected static string $resource = FrameResource::class;
    protected static string $view     = 'filament.frame-editor.page';

    protected $rules = [
        'record.name' => 'required|string|max:255',
    ];

    public Frame $record;

    public int    $canvasWidth  = 1200;
    public int    $canvasHeight = 1800;

    /** 'behind' = slot foto di BELAKANG overlay | 'front' = slot foto di DEPAN overlay */
    public string $photoLayer = 'behind';

    /** Photo slots [ { id, x, y, width, height } ] */
    public array $slots = [];

    public ?int $selectedSlotId = null;

    public int $editorX      = 0;
    public int $editorY      = 0;
    public int $editorWidth  = 400;
    public int $editorHeight = 300;

    public $frameFileUpload = null;
    public string $name     = '';

    public function getSubNavigation(): array
    {
        return [];
    }

    public function mount(Frame $record): void
    {
        $this->record = $record;
        $this->name   = $record->name ?? 'Untitled Frame';

        // Ukuran canvas dari database (sudah dipilih saat Buat Baru)
        $this->canvasWidth  = (int) ($record->canvas_width  ?? 1200);
        $this->canvasHeight = (int) ($record->canvas_height ?? 1800);
        $this->photoLayer   = $record->photo_layer ?? 'behind';

        $this->loadSlots();
    }

    public function loadSlots(): void
    {
        $raw        = $this->record->photo_slots ?? [];
        $this->slots = [];

        foreach ($raw as $slot) {
            $this->slots[] = [
                'id'     => (int) ($slot['id']     ?? (count($this->slots) + 1)),
                'x'      => (int) ($slot['x']      ?? 0),
                'y'      => (int) ($slot['y']      ?? 0),
                'width'  => (int) ($slot['width']  ?? 400),
                'height' => (int) ($slot['height'] ?? 300),
            ];
        }

        $this->slots = array_values($this->slots);
    }

    protected function nextSlotId(): int
    {
        if (empty($this->slots)) return 1;
        return max(array_column($this->slots, 'id')) + 1;
    }

    public function addSlot(): void
    {
        $id = $this->nextSlotId();
        $slot = [
            'id'     => $id,
            'x'      => (int) ($this->canvasWidth  / 4),
            'y'      => (int) ($this->canvasHeight / 4),
            'width'  => (int) ($this->canvasWidth  / 2),
            'height' => (int) ($this->canvasHeight / 4),
        ];
        $this->slots[] = $slot;
        $this->selectSlot($id);
    }

    public function selectSlot(int $id): void
    {
        foreach ($this->slots as $slot) {
            if ($slot['id'] === $id) {
                $this->selectedSlotId = $id;
                $this->editorX        = $slot['x'];
                $this->editorY        = $slot['y'];
                $this->editorWidth    = $slot['width'];
                $this->editorHeight   = $slot['height'];
                return;
            }
        }
        $this->selectedSlotId = null;
    }

    public function deselectSlot(): void
    {
        $this->selectedSlotId = null;
    }

    public function deleteSlot(int $id): void
    {
        $this->slots = array_values(array_filter(
            $this->slots,
            fn($s) => $s['id'] !== $id
        ));

        if ($this->selectedSlotId === $id) {
            $this->selectedSlotId = null;
        }
    }

    /** Toggle photo_layer antara 'behind' dan 'front' */
    public function togglePhotoLayer(): void
    {
        $this->photoLayer = $this->photoLayer === 'behind' ? 'front' : 'behind';
    }

    public function getSlotStyle(array $slot): array
    {
        return [
            'left'   => ($slot['x']      / $this->canvasWidth)  * 100,
            'top'    => ($slot['y']      / $this->canvasHeight) * 100,
            'width'  => ($slot['width']  / $this->canvasWidth)  * 100,
            'height' => ($slot['height'] / $this->canvasHeight) * 100,
        ];
    }

    /** Dipanggil dari JS alpine saat drag selesai */
    public function updateSlotPosition(int $id, int $x, int $y, int $width, int $height): void
    {
        // Clamp agar slot tidak melewati canvas
        $x = max(0, min($this->canvasWidth  - $width,  $x));
        $y = max(0, min($this->canvasHeight - $height, $y));
        $width  = max(20, min($this->canvasWidth,  $width));
        $height = max(20, min($this->canvasHeight, $height));

        foreach ($this->slots as $i => $slot) {
            if ($slot['id'] === $id) {
                $this->slots[$i]['x']      = $x;
                $this->slots[$i]['y']      = $y;
                $this->slots[$i]['width']  = $width;
                $this->slots[$i]['height'] = $height;

                if ($this->selectedSlotId === $id) {
                    $this->editorX      = $x;
                    $this->editorY      = $y;
                    $this->editorWidth  = $width;
                    $this->editorHeight = $height;
                }
                break;
            }
        }
    }

    protected function syncEditorToSlots(): void
    {
        if (!$this->selectedSlotId) return;

        $x = max(0, min($this->canvasWidth  - 20, $this->editorX));
        $y = max(0, min($this->canvasHeight - 20, $this->editorY));
        $w = max(20, min($this->canvasWidth,       $this->editorWidth));
        $h = max(20, min($this->canvasHeight,      $this->editorHeight));

        foreach ($this->slots as $i => $slot) {
            if ($slot['id'] === $this->selectedSlotId) {
                $this->slots[$i]['x']      = $x;
                $this->slots[$i]['y']      = $y;
                $this->slots[$i]['width']  = $w;
                $this->slots[$i]['height'] = $h;
                break;
            }
        }
    }

    public function updatedEditorX(): void
    {
        $this->syncEditorToSlots();
    }
    public function updatedEditorY(): void
    {
        $this->syncEditorToSlots();
    }
    public function updatedEditorWidth(): void
    {
        $this->syncEditorToSlots();
    }
    public function updatedEditorHeight(): void
    {
        $this->syncEditorToSlots();
    }

    public function saveSlots(): void
    {
        $this->syncEditorToSlots();

        $data = [
            'name'          => $this->name,
            'photo_slots'   => array_values($this->slots),
            'canvas_width'  => $this->canvasWidth,
            'canvas_height' => $this->canvasHeight,
            'photo_layer'   => $this->photoLayer,
        ];

        if ($this->frameFileUpload) {
            $path = $this->frameFileUpload->store('frames/files', 'public');
            $data['frame_file'] = $path;
            if (!$this->record->preview_image) {
                $data['preview_image'] = $path;
            }
            $this->frameFileUpload = null;
        }

        $this->record->update($data);

        Notification::make()
            ->title('Layout bingkai berhasil disimpan')
            ->success()
            ->send();
    }
}
