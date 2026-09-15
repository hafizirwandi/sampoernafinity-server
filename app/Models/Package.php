<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Package extends Model
{
    public const DURATION_UNITS = [
        'day' => 'Harian',
        'week' => 'Mingguan',
        'month' => 'Bulanan',
        'year' => 'Tahunan',
    ];

    protected $fillable = [
        'name',
        'slug',
        'price',
        'duration_unit',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(PackageSubscription::class);
    }

    public function durationLabel(): string
    {
        return self::DURATION_UNITS[$this->duration_unit] ?? $this->duration_unit;
    }

    public function calculateEndDate(Carbon $from): Carbon
    {
        return match ($this->duration_unit) {
            'day' => $from->copy()->addDay(),
            'week' => $from->copy()->addWeek(),
            'year' => $from->copy()->addYear(),
            default => $from->copy()->addMonth(),
        };
    }
}
