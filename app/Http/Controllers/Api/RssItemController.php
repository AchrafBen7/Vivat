<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RssItemResource;
use App\Models\RssItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RssItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RssItem::query()->with(['rssFeed', 'category', 'enrichedItem']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->string('category_id'));
        }
        if ($request->filled('rss_feed_id')) {
            $query->where('rss_feed_id', $request->string('rss_feed_id'));
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $items = $query->orderBy('fetched_at', 'desc')->paginate($perPage);

        return RssItemResource::collection($items);
    }

    public function show(RssItem $rss_item): RssItemResource
    {
        $rss_item->load(['rssFeed', 'category', 'enrichedItem']);

        return new RssItemResource($rss_item);
    }
}
