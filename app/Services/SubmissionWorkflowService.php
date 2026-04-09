<?php

namespace App\Services;

use App\Mail\SubmissionChangesRequestedMail;
use App\Mail\SubmissionRejectedMail;
use App\Models\Article;
use App\Models\Submission;
use App\Models\SubmissionStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubmissionWorkflowService
{
    public function __construct(
        private readonly SubmissionPublishingService $publishingService,
    ) {}

    /**
     * Rédacteur soumet son article.
     * draft | changes_requested → submitted
     */
    public function submit(Submission $submission, User $author): void
    {
        $submission->transitionTo(
            newStatus: 'submitted',
            triggeredBy: $author->id,
            triggerSource: 'author',
        );

        // Notifier les admins/modérateurs
        $this->notifyModerators($submission);
    }

    /**
     * Modérateur ouvre la soumission pour review.
     * submitted | pending → under_review
     */
    public function startReview(Submission $submission, User $moderator): void
    {
        if ($submission->status === 'under_review') {
            return; // déjà en review, idempotent
        }

        $submission->transitionTo(
            newStatus: 'under_review',
            triggeredBy: $moderator->id,
            triggerSource: 'moderator',
        );
    }

    /**
     * Modérateur demande des modifications.
     * under_review → changes_requested
     */
    public function requestChanges(Submission $submission, User $moderator, string $note): void
    {
        $submission->update(['reviewer_notes' => $note]);

        $submission->transitionTo(
            newStatus: 'changes_requested',
            triggeredBy: $moderator->id,
            triggerSource: 'moderator',
            reason: $note,
        );

        Mail::to($submission->user->email)
            ->queue(new SubmissionChangesRequestedMail($submission));
    }

    /**
     * Modérateur rejette définitivement la soumission.
     * under_review | price_proposed → rejected
     */
    public function reject(Submission $submission, User $moderator, string $reason): void
    {
        $submission->update([
            'reviewer_notes' => $reason,
            'reviewed_by'    => $moderator->id,
            'reviewed_at'    => now(),
        ]);

        $submission->transitionTo(
            newStatus: 'rejected',
            triggeredBy: $moderator->id,
            triggerSource: 'moderator',
            reason: $reason,
        );

        Mail::to($submission->user->email)
            ->queue(new SubmissionRejectedMail($submission));
    }

    /**
     * Après paiement confirmé, publie l'article.
     * payment_succeeded → published
     */
    public function publishAfterPayment(Submission $submission): Article
    {
        $article = DB::transaction(function () use ($submission): Article {
            $quote = $submission->quote()->latest('created_at')->first();

            $article = $this->publishingService->approveAndPublish(
                submission: $submission,
                data: [
                    'category_id'  => $submission->category_id,
                    'article_type' => $quote?->article_type ?: 'standard',
                    'reviewed_by'  => $submission->reviewed_by,
                    'reviewed_at'  => $submission->reviewed_at ?? now(),
                    'notes'        => $submission->reviewer_notes,
                ],
                reviewer: null,
            );

            $submission->transitionTo(
                newStatus: 'published',
                triggerSource: 'stripe_webhook',
                metadata: ['article_id' => $article->id],
            );

            return $article;
        });

        Log::info('Submission published after payment', [
            'submission_id' => $submission->id,
            'article_id'    => $article->id,
        ]);

        return $article;
    }

    private function notifyModerators(Submission $submission): void
    {
        $adminEmail = config('mail.admin_address');
        if (! $adminEmail) {
            return;
        }

        Mail::to($adminEmail)->queue(new \App\Mail\SubmissionReceivedMail($submission));
    }
}
