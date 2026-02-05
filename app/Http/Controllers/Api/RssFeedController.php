<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRssFeedRequest;
use App\Http\Requests\UpdateRssFeedRequest;
use App\Http\Resources\RssFeedResource;
use App\Models\RssFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RssFeedController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RssFeed::query()->with(['source', 'category']);
        if ($request->boolean('active')) {
            $query->active();
        }
        $feeds = $query->orderBy('created_at', 'desc')->get();

        return RssFeedResource::collection($feeds);
    }

    public function store(StoreRssFeedRequest $request): JsonResponse|RssFeedResource
    {
        $this->authorize('create', RssFeed::class);
        $feed = RssFeed::create($request->validated());

        return (new RssFeedResource($feed->load(['source', 'category'])))->response()->setStatusCode(201);
    }

    public function show(RssFeed $rss_feed): RssFeedResource
    {
        $rss_feed->load(['source', 'category']);

        return new RssFeedResource($rss_feed);
    }

    public function update(UpdateRssFeedRequest $request, RssFeed $rss_feed): RssFeedResource
    {
        $this->authorize('update', $rss_feed);
        $rss_feed->update($request->validated());

        return new RssFeedResource($rss_feed->fresh(['source', 'category']));
    }

    public function destroy(RssFeed $rss_feed): JsonResponse
    {
        $this->authorize('delete', $rss_feed);
        $rss_feed->delete();

        return response()->json(null, 204);
    }
}
