<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'is_free',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }
}
