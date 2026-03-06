<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminSubmissionController extends Controller
{
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
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->approve(
            reviewerId: $request->user()->id,
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'message'    => 'Soumission approuvée.',
            'submission' => new SubmissionResource($submission->fresh(['user', 'category', 'reviewer'])),
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
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        $submission->reject(
            reviewerId: $request->user()->id,
            notes: $validated['notes']
        );

        return response()->json([
            'message'    => 'Soumission rejetée.',
            'submission' => new SubmissionResource($submission->fresh(['user', 'category', 'reviewer'])),
        ]);
    }
}
