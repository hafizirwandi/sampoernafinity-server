<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\GiftCategory;
use Illuminate\Http\JsonResponse;

class GiftController extends Controller
{
    /**
     * Full gift/sticker catalog for the desktop app to sync and cache
     * locally (including downloading each image), so it's fast and works
     * offline once someone is live.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'categories' => GiftCategory::query()
                ->orderBy('type')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'type', 'name', 'slug', 'sort_order']),
            'gifts' => Gift::query()
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'gift_category_id', 'type', 'tiktok_id', 'name', 'coin', 'image_url']),
        ]);
    }
}
