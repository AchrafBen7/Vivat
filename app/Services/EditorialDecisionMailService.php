<?php

namespace App\Services;

use App\Mail\SubmissionApprovedMail;
use App\Mail\SubmissionRefundedMail;
use App\Mail\SubmissionRejectedMail;
use App\Models\Article;
use App\Models\Payment;
use App\Models\Submission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EditorialDecisionMailService
{
    public function sendApproved(Submission $submission, Article $article): void
    {
        $this->safeSend(
            $submission,
            fn () => Mail::to($submission->user->email)->send(new SubmissionApprovedMail($submission, $article))
        );
    }

    public function sendRejected(Submission $submission): void
    {
        $this->safeSend(
            $submission,
            fn () => Mail::to($submission->user->email)->send(new SubmissionRejectedMail($submission))
        );
    }

    public function sendRefunded(Payment $payment): void
    {
        $payment->loadMissing('submission.user');

        $submission = $payment->submission;

        if (! $submission) {
            return;
        }

        $this->safeSend(
            $submission,
            fn () => Mail::to($submission->user->email)->send(new SubmissionRefundedMail($payment))
        );
    }

    private function safeSend(Submission $submission, callable $callback): void
    {
        if (! $submission->relationLoaded('user')) {
            $submission->load('user');
        }

        if (! $submission->user?->email) {
            return;
        }

        try {
            $callback();
        } catch (\Throwable $exception) {
            Log::warning('Failed to send editorial decision email.', [
                'submission_id' => $submission->id,
                'email' => $submission->user?->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
