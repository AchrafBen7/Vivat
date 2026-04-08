<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Submission;
use App\Services\PublicationQuoteService;
use App\Services\SubmissionPublishingService;
use App\Services\SubmissionWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminSubmissionController extends Controller
{
    public function __construct(
        private readonly SubmissionPublishingService $submissionPublishingService,
        private readonly SubmissionWorkflowService   $workflowService,
        private readonly PublicationQuoteService     $quoteService,
    ) {}

    /**
     * GET /api/admin/submissions
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Submission::with(['user', 'category', 'reviewer', 'quote']);

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
        $submission->load(['user', 'category', 'reviewer', 'quote.preset', 'submissionPayments', 'statusLogs']);

        return new SubmissionResource($submission);
    }

    /**
     * POST /api/admin/submissions/{submission}/start-review
     * Ouvre la soumission pour review (submitted | pending → under_review).
     */
    public function startReview(Request $request, Submission $submission): JsonResponse
    {
        if (! in_array($submission->status, ['submitted', 'pending'], true)) {
            return response()->json(['message' => 'Seules les soumissions soumises peuvent être ouvertes en review.'], 422);
        }

        $this->workflowService->startReview($submission, $request->user());

        return response()->json([
            'message'    => 'Revue démarrée.',
            'submission' => new SubmissionResource($submission->fresh(['user', 'category'])),
        ]);
    }

    /**
     * POST /api/admin/submissions/{submission}/request-changes
     * under_review → changes_requested
     */
    public function requestChanges(Request $request, Submission $submission): JsonResponse
    {
        if ($submission->status !== 'under_review') {
            return response()->json(['message' => 'La soumission doit être en cours de revue.'], 422);
        }

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:3000'],
        ]);

        $this->workflowService->requestChanges($submission, $request->user(), $validated['note']);

        return response()->json([
            'message'    => 'Corrections demandées. Le rédacteur a été notifié.',
            'submission' => new SubmissionResource($submission->fresh()),
        ]);
    }

    /**
     * POST /api/admin/submissions/{submission}/propose-price
     * under_review → price_proposed → awaiting_payment
     */
    public function proposePrice(Request $request, Submission $submission): JsonResponse
    {
        if ($submission->status !== 'under_review') {
            return response()->json(['message' => 'La soumission doit être en cours de revue pour proposer un prix.'], 422);
        }

        $validated = $request->validate([
            'amount_cents'    => ['required', 'integer', 'min:100'],   // minimum 1,00 €
            'currency'        => ['nullable', 'string', 'size:3'],
            'price_preset_id' => ['nullable', 'uuid', 'exists:price_presets,id'],
            'note_to_author'  => ['nullable', 'string', 'max:2000'],
            'expiry_days'     => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $quote = $this->quoteService->propose(
            submission:    $submission,
            moderator:     $request->user(),
            amountCents:   $validated['amount_cents'],
            currency:      $validated['currency'] ?? 'eur',
            pricePresetId: $validated['price_preset_id'] ?? null,
            noteToAuthor:  $validated['note_to_author'] ?? null,
            expiryDays:    $validated['expiry_days'] ?? 7,
        );

        // Envoyer l'email au rédacteur
        \Mail::to($submission->user->email)
            ->queue(new \App\Mail\QuoteSentMail($submission, $quote));

        return response()->json([
            'message'    => "Proposition de {$quote->formatted_amount} envoyée au rédacteur. Il a {$validated['expiry_days']} jour(s) pour payer.",
            'quote_id'   => $quote->id,
            'submission' => new SubmissionResource($submission->fresh()),
        ]);
    }

    /**
     * POST /api/admin/submissions/{submission}/reject
     * under_review | price_proposed → rejected
     */
    public function reject(Request $request, Submission $submission): JsonResponse
    {
        $allowedStatuses = ['pending', 'submitted', 'under_review', 'price_proposed', 'changes_requested'];

        if (! in_array($submission->status, $allowedStatuses, true)) {
            return response()->json(['message' => 'Cette soumission ne peut plus être rejetée.'], 422);
        }

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        // Forcer under_review si pas encore en review (compat ancien workflow)
        if (in_array($submission->status, ['pending', 'submitted'], true)) {
            $this->workflowService->startReview($submission, $request->user());
            $submission->refresh();
        }

        $this->workflowService->reject($submission, $request->user(), $validated['notes']);

        return response()->json([
            'message'    => 'Soumission rejetée.',
            'submission' => new SubmissionResource($submission->fresh(['user', 'category', 'reviewer'])),
        ]);
    }

    /**
     * POST /api/admin/submissions/{submission}/approve  (compat ancien workflow)
     * Approuve et publie directement (sans paiement) — gardé pour compat.
     */
    public function approve(Request $request, Submission $submission): JsonResponse
    {
        if (! in_array($submission->status, ['pending', 'submitted', 'under_review', 'payment_succeeded'], true)) {
            return response()->json([
                'message' => 'Seules les soumissions en attente ou dont le paiement est confirmé peuvent être approuvées.',
            ], 422);
        }

        $validated = $request->validate([
            'category_id'  => ['nullable', 'uuid', 'exists:categories,id'],
            'article_type' => ['nullable', 'in:hot_news,long_form,standard'],
            'reviewed_by'  => ['nullable', 'uuid', 'exists:users,id'],
            'reviewed_at'  => ['nullable', 'date'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ]);

        $article = $this->submissionPublishingService->approveAndPublish(
            submission: $submission,
            data: [
                'category_id'  => $validated['category_id'] ?? $submission->category_id,
                'article_type' => $validated['article_type'] ?? 'standard',
                'reviewed_by'  => $validated['reviewed_by'] ?? $request->user()->id,
                'reviewed_at'  => $validated['reviewed_at'] ?? now(),
                'notes'        => $validated['notes'] ?? null,
            ],
            reviewer: $request->user(),
        );

        return response()->json([
            'message'    => 'Soumission approuvée et article publié.',
            'submission' => new SubmissionResource($submission->fresh(['user', 'category', 'reviewer'])),
            'article'    => [
                'id'           => $article->id,
                'slug'         => $article->slug,
                'status'       => $article->status,
                'article_type' => $article->article_type,
            ],
        ]);
    }
}
