<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'plate_number',
        'customer_name',
        'motorcycle_type',
        'service_date',
        'service_time',
        'service_package_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'service_date' => 'date:Y-m-d',
    ];

    /**
     * Mengambil data service untuk booking.
     */
    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    /**
     * Scope untuk tanggal dan jam slot tertentu.
     */
    public function scopeForSlot(Builder $query, string $date, string $time): Builder
    {
        return $query->whereDate('service_date', $date)
            ->where('service_time', $time);
    }
}
