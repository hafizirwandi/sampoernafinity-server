<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftCategory extends Model
{
    public const TYPES = [
        'gift' => 'Gift',
        'sticker' => 'Stiker',
    ];

    protected $fillable = [
        'type',
        'name',
        'slug',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(Gift::class);
    }
}
