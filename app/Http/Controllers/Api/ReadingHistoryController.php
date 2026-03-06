<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadingHistoryController extends Controller
{
    /**
     * POST /api/public/reading-progress
     * Save or update reading progress for an article.
     * Works with session_id (cookie) or user_id (auth).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'uuid', 'exists:articles,id'],
            'session_id' => ['required_without:user_id', 'nullable', 'string', 'max:100'],
            'progress'   => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $userId = $request->user()?->id;
        $sessionId = $validated['session_id'] ?? null;

        $history = ReadingHistory::updateOrCreate(
            [
                'article_id' => $validated['article_id'],
                'user_id'    => $userId,
                'session_id' => $userId ? null : $sessionId,
            ],
            [
                'progress' => $validated['progress'],
                'read_at'  => now(),
            ]
        );

        return response()->json([
            'message'  => 'Progression sauvegardée.',
            'progress' => $history->progress,
        ]);
    }

    /**
     * GET /api/public/reading-progress?session_id=xxx
     * Get reading progress for all articles (for resume feature).
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $sessionId = $request->input('session_id');

        if (! $userId && ! $sessionId) {
            return response()->json(['data' => []]);
        }

        $histories = ReadingHistory::forViewer($userId, $sessionId)
            ->with('article:id,title,slug,reading_time')
            ->orderByDesc('read_at')
            ->limit(50)
            ->get()
            ->map(fn ($h) => [
                'article_id' => $h->article_id,
                'article'    => $h->article ? [
                    'id'           => $h->article->id,
                    'title'        => $h->article->title,
                    'slug'         => $h->article->slug,
                    'reading_time' => $h->article->reading_time,
                ] : null,
                'progress'   => $h->progress,
                'read_at'    => $h->read_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $histories]);
    }
}
