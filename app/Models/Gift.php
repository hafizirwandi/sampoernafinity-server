<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gift extends Model
{
    protected $fillable = [
        'gift_category_id',
        'type',
        'tiktok_id',
        'name',
        'coin',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'coin' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GiftCategory::class, 'gift_category_id');
    }
}
