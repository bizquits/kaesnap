<?php

namespace App\Filament\Resources\FrameResource\Pages;

use App\Filament\Resources\FrameResource;
use App\Models\Frame;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class ListFrames extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = FrameResource::class;

    /**
     * Override view agar modal bisa mengakses variabel Livewire secara langsung.
     * Path: resources/views/filament/resources/frame-resource/pages/list-frames.blade.php
     */
    protected static string $view = 'filament.resources.frame-resource.pages.list-frames';

    // ── Modal state ──────────────────────────────────────────────────────────
    public string $modalFrameName       = '';
    public string $modalFrameSize       = '1200x1800';
    public $modalFrameOverlay           = null;
    public bool   $showCreateModal      = false;
    public string $modalValidationError = '';
    // ─────────────────────────────────────────────────────────────────────────

    public function getHeading(): string | Htmlable
    {
        return 'Manajemen Bingkai';
    }

    public function getSubheading(): ?string
    {
        return 'Buat dan kelola bingkai photobooth Anda';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openCreateModal')
                ->label('Buat Baru')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->action(fn() => $this->openModal()),
        ];
    }

    // ── Modal methods ────────────────────────────────────────────────────────

    public function openModal(): void
    {
        $this->modalFrameName       = '';
        $this->modalFrameSize       = '1200x1800';
        $this->modalFrameOverlay    = null;
        $this->modalValidationError = '';
        $this->showCreateModal      = true;
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
    }

    public function selectSize(string $size): void
    {
        $this->modalFrameSize = $size;
    }

    /**
     * Validasi, simpan frame baru, redirect ke FrameLayout.
     */
    public function submitAndGoToLayout(): void
    {
        $this->modalValidationError = '';

        if (trim($this->modalFrameName) === '') {
            $this->modalValidationError = 'Nama bingkai wajib diisi.';
            return;
        }

        if (!$this->modalFrameOverlay) {
            $this->modalValidationError = 'File overlay (PNG) wajib diunggah.';
            return;
        }

        [$w, $h] = explode('x', $this->modalFrameSize);

        $path = $this->modalFrameOverlay->store('frames/files', 'public');

        $frame = Frame::create([
            'user_id'       => Auth::id(),
            'name'          => trim($this->modalFrameName),
            'frame_file'    => $path,
            'preview_image' => $path,
            'is_active'     => true,
            'canvas_width'  => (int) $w,
            'canvas_height' => (int) $h,
            'photo_layer'   => 'behind',
        ]);

        $this->showCreateModal = false;

        $this->redirect(
            FrameResource::getUrl('layout', ['record' => $frame])
        );
    }
}
