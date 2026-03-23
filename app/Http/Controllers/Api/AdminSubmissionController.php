<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Submission;
use App\Services\SubmissionPublishingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminSubmissionController extends Controller
{
    public function __construct(
        private readonly SubmissionPublishingService $submissionPublishingService,
    ) {}

    /**
     * GET /api/admin/submissions — liste des soumissions (filter par status)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Submission::with(['user', 'category', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return SubmissionResource::collection(
            $query->orderByDesc('created_at')->paginate(20)
        );
    }

    /**
     * GET /api/admin/submissions/{submission}
     */
    public function show(Submission $submission): SubmissionResource
    {
        $submission->load(['user', 'category', 'reviewer']);

        return new SubmissionResource($submission);
    }

    /**
     * POST /api/admin/submissions/{submission}/approve
     */
    public function approve(Request $request, Submission $submission): JsonResponse
    {
        if ($submission->status !== 'pending') {
            return response()->json([
                'message' => 'Seules les soumissions en attente peuvent être approuvées.',
            ], 422);
        }

        $validated = $request->validate([
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'article_type' => ['nullable', 'in:hot_news,long_form,standard'],
            'reviewed_by' => ['nullable', 'uuid', 'exists:users,id'],
            'reviewed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $article = $this->submissionPublishingService->approveAndPublish(
            submission: $submission,
            data: [
                'category_id' => $validated['category_id'] ?? $submission->category_id,
                'article_type' => $validated['article_type'] ?? 'standard',
                'reviewed_by' => $validated['reviewed_by'] ?? $request->user()->id,
                'reviewed_at' => $validated['reviewed_at'] ?? now(),
                'notes' => $validated['notes'] ?? null,
            ],
            reviewer: $request->user(),
        );

        return response()->json([
            'message'    => 'Soumission approuvée et article publié.',
            'submission' => new SubmissionResource($submission->fresh(['user', 'category', 'reviewer'])),
            'article' => [
                'id' => $article->id,
                'slug' => $article->slug,
                'status' => $article->status,
                'article_type' => $article->article_type,
            ],
        ]);
    }

    /**
     * POST /api/admin/submissions/{submission}/reject
     */
    public function reject(Request $request, Submission $submission): JsonResponse
    {
        if ($submission->status !== 'pending') {
            return response()->json([
                'message' => 'Seules les soumissions en attente peuvent être rejetées.',
            ], 422);
        }

        $validated = $request->validate([
            'reviewed_by' => ['nullable', 'uuid', 'exists:users,id'],
            'reviewed_at' => ['nullable', 'date'],
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        $this->submissionPublishingService->reject(
            submission: $submission,
            data: [
                'reviewed_by' => $validated['reviewed_by'] ?? $request->user()->id,
                'reviewed_at' => $validated['reviewed_at'] ?? now(),
                'notes' => $validated['notes'],
            ],
            reviewer: $request->user(),
        );

        return response()->json([
            'message'    => 'Soumission rejetée.',
            'submission' => new SubmissionResource($submission->fresh(['user', 'category', 'reviewer'])),
        ]);
    }
}
