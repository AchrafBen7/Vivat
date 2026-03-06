<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cluster;
use App\Models\ClusterItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClusterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Cluster::with(['category', 'clusterItems']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->string('category_id'));
        }

        $clusters = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $clusters]);
    }

    public function show(Cluster $cluster): JsonResponse
    {
        $cluster->load(['category', 'clusterItems.rssItem.enrichedItem']);

        return response()->json(['data' => $cluster]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|uuid|exists:categories,id',
            'label' => 'required|string|max:255',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            'status' => 'nullable|in:pending,processing,generated,failed',
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'uuid|exists:rss_items,id',
        ]);

        $cluster = Cluster::create([
            'category_id' => $validated['category_id'] ?? null,
            'label' => $validated['label'],
            'keywords' => $validated['keywords'] ?? [],
            'status' => $validated['status'] ?? 'pending',
        ]);

        // Attacher les items au cluster
        if (! empty($validated['item_ids'])) {
            foreach ($validated['item_ids'] as $itemId) {
                ClusterItem::create([
                    'cluster_id' => $cluster->id,
                    'rss_item_id' => $itemId,
                ]);
            }
        }

        return response()->json(['data' => $cluster->load(['category', 'clusterItems'])], 201);
    }

    public function update(Request $request, Cluster $cluster): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            'status' => 'nullable|in:pending,processing,generated,failed',
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'uuid|exists:rss_items,id',
        ]);

        $cluster->update(collect($validated)->except('item_ids')->toArray());

        if (isset($validated['item_ids'])) {
            $cluster->clusterItems()->delete();
            foreach ($validated['item_ids'] as $itemId) {
                ClusterItem::create([
                    'cluster_id' => $cluster->id,
                    'rss_item_id' => $itemId,
                ]);
            }
        }

        return response()->json(['data' => $cluster->fresh(['category', 'clusterItems'])]);
    }

    public function destroy(Cluster $cluster): JsonResponse
    {
        $cluster->clusterItems()->delete();
        $cluster->delete();

        return response()->json(null, 204);
    }
}
