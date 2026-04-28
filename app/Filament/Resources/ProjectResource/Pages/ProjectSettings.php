<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Frame;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProjectSettings extends Page implements HasForms
{
    use InteractsWithForms;


    protected static string $resource = ProjectResource::class;
    protected static string $view = 'filament.projects.settings';

    public function getSubNavigation(): array
    {
        return [];
    }

    /**
     * =========================
     * FORM REGISTRATION
     * =========================
     */
    protected function getForms(): array
    {
        return [
            'form',         // General
            'pricingForm',  // Pricing
            'frameForm',    // Bingkai
        ];
    }

    /**
     * =========================
     * STATE
     * =========================
     */
    public Project $record;

    public array $data = [];     // General
    public array $pricing = [];  // Pricing
    public array $frames = [];   // Bingkai

    /**
     * =========================
     * MOUNT
     * =========================
     */
    public function mount(Project $record): void
    {
        $this->record = $record;

        // General
        $this->form->fill(
            $record->only([
                'name',
                'description',
                'is_active',
            ])
        );

        // Pricing
        $setting = $record->setting;
        $pricePerSession = $setting?->price_per_session ?? []; // sekarang array
        $this->pricing = $setting
            ? array_merge($setting->only([
                'copies',
                'max_retakes',
                'countdown_seconds',
                'auto_print',
            ]), [
                'copy_prices'    => $setting->copy_prices ?? 0, // integer
                'price_slot_1'   => $pricePerSession['1'] ?? 0,
                'price_slot_2'   => $pricePerSession['2'] ?? null,
                'price_slot_3'   => $pricePerSession['3'] ?? null,
                'price_slot_4'   => $pricePerSession['4'] ?? null,
            ])
            : [
                'copy_prices'    => 0,
                'copies'         => 1,
                'price_slot_1'   => 0,
                'price_slot_2'   => null,
                'price_slot_3'   => null,
                'price_slot_4'   => null,
                'max_retakes'    => 1,
                'countdown_seconds' => 3,
                'auto_print'     => true,
            ];

        // Frames
        $this->frames = $record->frames()
            ->pluck('frames.id')
            ->toArray();
    }

    /**
     * =========================
     * GENERAL FORM
     * =========================
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Project Name')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3),

                Forms\Components\Toggle::make('is_active')
                    ->label('Project Active'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->record->update($this->form->getState());
    }

    /**
     * =========================
     * PRICING FORM
     * =========================
     */
    public function pricingForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('info_slot')
                    ->label('Harga Berdasarkan Slot Foto')
                    ->content('Atur harga sesi berdasarkan jumlah slot foto pada frame yang dipilih user.'),

                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('price_slot_1')
                            ->label('1 slot foto')->numeric()->prefix('Rp')->default(0),
                        Forms\Components\TextInput::make('price_slot_2')
                            ->label('2 slot foto')->numeric()->prefix('Rp'),
                        Forms\Components\TextInput::make('price_slot_3')
                            ->label('3 slot foto')->numeric()->prefix('Rp'),
                        Forms\Components\TextInput::make('price_slot_4')
                            ->label('4 slot foto')->numeric()->prefix('Rp'),
                    ]),

                Forms\Components\TextInput::make('copy_prices')
                    ->label('Harga per Eksemplar Tambahan')
                    ->helperText('Harga flat yang ditambahkan per eksemplar cetak (integer). Contoh: 5000')
                    ->numeric()->prefix('Rp')->default(0),

                Forms\Components\TextInput::make('max_retakes')
                    ->label('Max Retakes')->numeric()->minValue(0)->required(),

                Forms\Components\TextInput::make('countdown_seconds')
                    ->label('Timer Take Photo (detik)')
                    ->numeric()->minValue(1)->maxValue(10)->default(3)->required(),

                Forms\Components\Toggle::make('auto_print')
                    ->label('Auto Print After Session'),
            ])
            ->statePath('pricing');
    }

    public function savePricing(): void
    {
        $pricing = $this->pricing;

        // Bangun array price_per_session keyed by jumlah slot
        $pricePerSession = [];
        foreach ([1, 2, 3, 4] as $n) {
            $val = $pricing["price_slot_{$n}"] ?? null;
            if ($val !== null && $val !== '') {
                $pricePerSession[(string) $n] = (int) $val;
            }
        }

        $this->record->setting()->updateOrCreate(
            ['project_id' => $this->record->id],
            [
                'price_per_session'  => $pricePerSession, // array
                'copy_prices'        => (int) ($pricing['copy_prices'] ?? 0), // integer
                'copies'             => $pricing['copies'] ?? 1,
                'max_retakes'        => $pricing['max_retakes'],
                'countdown_seconds'  => $pricing['countdown_seconds'],
                'auto_print'         => $pricing['auto_print'] ?? false,
            ]
        );
    }

    /**
     * =========================
     * FRAME FORM
     * =========================
     */
    public function frameForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\CheckboxList::make('frames')
                    ->label('Available Frames')
                    ->options(
                        Frame::where('user_id', Auth::id())
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->columns(2)
                    ->helperText('Select frames available for this project'),
            ])
            ->statePath('frames');
    }

    public function saveFrames(): void
    {
        $this->record->frames()->sync(
            collect($this->frames)
                ->mapWithKeys(fn($id) => [
                    $id => ['is_active' => true],
                ])
                ->toArray()
        );
    }

    /**
     * Frames yang bisa dipilih untuk project (untuk tampilan dengan preview).
     */
    public function getAvailableFrames()
    {
        return Frame::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Save all sections (General, Pricing, Frames) at once.
     */
    public function saveAll(): void
    {
        $this->save();
        $this->savePricing();
        $this->saveFrames();

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    /**
     * =========================
     * BACK
     * =========================
     */
    public function back()
    {
        return redirect()->route(
            'filament.admin.resources.projects.index'
        );
    }
}
