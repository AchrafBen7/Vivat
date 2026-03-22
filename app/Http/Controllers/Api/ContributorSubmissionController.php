<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Submission;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ContributorSubmissionController extends Controller
{
    /**
     * GET /api/contributor/submissions — historique des soumissions du contributeur
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $submissions = Submission::forUser($request->user()->id)
            ->with('category')
            ->orderByDesc('created_at')
            ->paginate(15);

        return SubmissionResource::collection($submissions);
    }

    /**
     * GET /api/contributor/submissions/{submission}
     */
    public function show(Request $request, Submission $submission): SubmissionResource|JsonResponse
    {
        if ($submission->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $submission->load('category');

        return new SubmissionResource($submission);
    }

    /**
     * POST /api/contributor/submissions — soumettre un article
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'excerpt'     => ['nullable', 'string', 'max:500'],
            'content'     => ['required', 'string', 'min:100'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'submit'      => ['sometimes', 'boolean'],
        ]);

        $submission = Submission::create([
            'user_id'     => $request->user()->id,
            'title'       => $validated['title'],
            'excerpt'     => $validated['excerpt'] ?? null,
            'content'     => $validated['content'],
            'category_id' => $validated['category_id'] ?? null,
            'reading_time' => $validated['reading_time'] ?? 5,
            'status'      => ! empty($validated['submit']) ? 'pending' : 'draft',
            'cover_image_path' => $request->hasFile('cover_image')
                ? $this->storeSubmissionCoverImage($request->file('cover_image'))
                : null,
        ]);

        $submission->load('category');

        return (new SubmissionResource($submission))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT /api/contributor/submissions/{submission} — modifier avant soumission
     */
    public function update(Request $request, Submission $submission): SubmissionResource|JsonResponse
    {
        if ($submission->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (! in_array($submission->status, ['draft', 'rejected'])) {
            return response()->json([
                'message' => 'Impossible de modifier une soumission en cours de validation ou déjà approuvée.',
            ], 422);
        }

        $validated = $request->validate([
            'title'       => ['sometimes', 'string', 'max:255'],
            'excerpt'     => ['nullable', 'string', 'max:500'],
            'content'     => ['sometimes', 'string', 'min:100'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'submit'      => ['sometimes', 'boolean'],
        ]);

        $data = collect($validated)->except('submit')->toArray();

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $this->storeSubmissionCoverImage($request->file('cover_image'));
        }

        if (! empty($validated['submit'])) {
            $data['status'] = 'pending';
        }

        $submission->update($data);
        $submission->load('category');

        return new SubmissionResource($submission);
    }

    /**
     * DELETE /api/contributor/submissions/{submission} — supprimer un brouillon
     */
    public function destroy(Request $request, Submission $submission): JsonResponse
    {
        if ($submission->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($submission->status !== 'draft') {
            return response()->json([
                'message' => 'Seuls les brouillons peuvent être supprimés.',
            ], 422);
        }

        $submission->delete();

        return response()->json(null, 204);
    }

    private function storeSubmissionCoverImage(UploadedFile $file): string
    {
        $directory = public_path('uploads/submissions');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return '/uploads/submissions/' . $filename;
    }
}
