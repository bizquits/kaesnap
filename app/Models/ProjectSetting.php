<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'price_per_session',
        'copy_prices',
        'copies',
        'max_retakes',
        'countdown_seconds',
        'auto_print',
    ];

    protected $casts = [
        'price_per_session' => 'array',
        'copy_prices' => 'integer',
    ];

    /**
     * Get price for given copy count. Falls back to price_per_session if copy_prices not set.
     */
    public function getPriceForCopies(int $copies): float
    {
        return (float) ($this->copy_prices ?? 0) * $copies;
    }

    /**
     * Kembalikan harga berdasarkan jumlah slot foto frame yang dipilih.
     * $slotCount = jumlah slot foto pada frame yang dipilih user.
     */
    public function getPriceBySlot(int $slotCount): float
    {
        $prices = $this->price_per_session; // array ['1' => 10000, '2' => 15000]
        if (is_array($prices) && isset($prices[(string) $slotCount])) {
            return (float) $prices[(string) $slotCount];
        }
        // fallback: ambil harga slot 1 jika ada
        return (float) ($prices['1'] ?? 0);
    }

    public function getCopyPriceOptions(): array
    {
        // copy_prices sekarang integer: harga flat per eksemplar
        $perCopy = (int) ($this->copy_prices ?? 0);
        $base    = $this->getPriceBySlot(1); // default pakai slot 1
        // kembalikan [1 => base, 2 => base + perCopy, dst]
        // ini akan di-override di controller setelah frame dipilih
        return [1 => $base];
    }
}
