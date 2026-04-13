<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Payment;
use App\Models\PublicationQuote;
use App\Models\Submission;
use App\Services\AccountDeletionService;
use App\Services\SubmissionImageStorageService;
use App\Services\SubmissionPublishingService;
use App\Services\SubmissionWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

abstract class ContributorBaseController extends Controller
{
    public function __construct(
        protected readonly AccountDeletionService $accountDeletionService,
        protected readonly SubmissionPublishingService $submissionPublishingService,
        protected readonly SubmissionImageStorageService $submissionImageStorage,
        protected readonly SubmissionWorkflowService $submissionWorkflow,
    ) {}

    protected function hasPaidPublication(Submission $submission): bool
    {
        return Payment::query()
            ->where('submission_id', $submission->id)
            ->where('status', 'paid')
            ->exists();
    }

    protected function submissionAcceptedPayload(string $redirectUrl): array
    {
        return [
            'ok' => true,
            'status' => 'submitted',
            'redirect_url' => $redirectUrl,
            'notice' => [
                'title' => 'Article envoyé en vérification',
                'message' => 'Votre article a été transmis à notre équipe éditoriale. Après relecture, un prix vous sera proposé et vous pourrez alors finaliser la publication.',
            ],
        ];
    }

    protected function adminPublishedPayload(string $redirectUrl): array
    {
        return [
            'ok' => true,
            'status' => 'approved',
            'redirect_url' => $redirectUrl,
            'notice' => [
                'title' => 'Article publié',
                'message' => 'Votre article a été publié directement sans étape de paiement.',
            ],
        ];
    }

    protected function isAutosaveRequest(Request $request): bool
    {
        return $request->boolean('autosave') || $request->header('X-Autosave') === '1';
    }

    protected function generateUniqueSubmissionSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug(trim($title)) ?: 'brouillon';
        $slug = $base;
        $suffix = 2;

        while (Submission::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function autosavePayload(Submission $submission): array
    {
        return [
            'ok' => true,
            'submission_id' => $submission->id,
            'status' => 'draft',
            'title' => $submission->title,
            'slug' => $submission->slug,
            'edit_url' => route('contributor.articles.edit', ['submission' => $submission->slug]),
            'preview_url' => route('contributor.articles.show', ['submission' => $submission->slug]),
            'notice' => [
                'title' => 'Brouillon sauvegardé',
                'message' => 'Votre dernière version a bien été enregistrée.',
            ],
        ];
    }

    protected function shouldForkRejectedSubmission(Submission $submission): bool
    {
        return $submission->status === 'rejected'
            && (
                filled($submission->payment_id)
                || filled($submission->reviewer_notes)
                || filled($submission->reviewed_by)
                || filled($submission->reviewed_at)
            );
    }

    protected function findExistingRevisionDraft(Submission $submission, string $userId): ?Submission
    {
        if (! $submission->published_article_id) {
            return null;
        }

        return Submission::query()
            ->where('user_id', $userId)
            ->where('published_article_id', $submission->published_article_id)
            ->where('status', 'draft')
            ->whereKeyNot($submission->id)
            ->latest('updated_at')
            ->first();
    }

    protected function createRevisionDraftFromRejected(Submission $submission, string $userId): Submission
    {
        return Submission::create([
            'user_id' => $userId,
            'title' => $submission->title,
            'slug' => $this->generateUniqueSubmissionSlug($submission->title),
            'excerpt' => $submission->excerpt,
            'content' => $submission->content,
            'category_id' => $submission->category_id,
            'language' => $submission->language ?? 'fr',
            'reading_time' => $submission->reading_time ?: 5,
            'status' => 'draft',
            'reviewer_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'payment_id' => null,
            'cover_image_url' => $submission->cover_image_url,
        ]);
    }

    protected function createRevisionDraftFromPublished(Submission $submission, string $userId): Submission
    {
        return Submission::create([
            'user_id' => $userId,
            'title' => $submission->title,
            'slug' => $this->generateUniqueSubmissionSlug($submission->title),
            'excerpt' => $submission->excerpt,
            'content' => $submission->content,
            'category_id' => $submission->category_id,
            'language' => $submission->language ?? 'fr',
            'reading_time' => $submission->reading_time ?: 5,
            'status' => 'draft',
            'reviewer_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'payment_id' => null,
            'published_article_id' => $submission->published_article_id,
            'depublication_requested_at' => null,
            'depublication_reason' => null,
            'cover_image_url' => $submission->cover_image_url,
        ]);
    }

    protected function contributorSubmissionRules(bool $isAutosave, bool $isSubmitting): array
    {
        if ($isAutosave) {
            return [
                'title' => ['nullable', 'string', 'max:255'],
                'excerpt' => ['nullable', 'string', 'max:500'],
                'content' => ['nullable', 'string'],
                'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
                'language' => ['nullable', 'in:fr,nl'],
                'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
                'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'status' => ['sometimes', 'in:draft,submitted'],
            ];
        }

        if ($isSubmitting) {
            return [
                'title' => ['required', 'string', 'min:12', 'max:255'],
                'excerpt' => ['required', 'string', 'min:30', 'max:500'],
                'content' => ['required', 'string', 'min:300'],
                'category_id' => ['required', 'uuid', 'exists:categories,id'],
                'language' => ['required', 'in:fr,nl'],
                'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
                'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'status' => ['sometimes', 'in:draft,submitted'],
            ];
        }

        return [
            'title' => ['required', 'string', 'min:8', 'max:255'],
            'excerpt' => ['nullable', 'string', 'min:20', 'max:500'],
            'content' => ['required', 'string', 'min:80'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'language' => ['required', 'in:fr,nl'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'status' => ['sometimes', 'in:draft,submitted'],
        ];
    }

    protected function contributorSubmissionMessages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.min' => 'Le titre doit contenir au moins 8 caractères.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'excerpt.required' => "L'extrait est obligatoire avant d'envoyer l'article.",
            'excerpt.min' => "L'extrait doit contenir au moins 20 caractères.",
            'excerpt.max' => "L'extrait ne peut pas dépasser 500 caractères.",
            'content.required' => 'Le contenu est obligatoire.',
            'content.min' => 'Le contenu est trop court pour être envoyé tel quel.',
            'category_id.required' => 'Veuillez choisir une rubrique pour votre article.',
            'category_id.exists' => 'La rubrique sélectionnée est invalide.',
            'language.required' => 'Veuillez choisir la langue de votre article.',
            'language.in' => 'La langue sélectionnée est invalide.',
            'reading_time.integer' => 'Le temps de lecture doit être un nombre entier.',
            'reading_time.min' => "Le temps de lecture doit être d'au moins 1 minute.",
            'reading_time.max' => 'Le temps de lecture ne peut pas dépasser 120 minutes.',
            'cover_image.image' => "L'image de couverture doit être une image valide.",
            'cover_image.mimes' => "L'image de couverture doit être au format JPG ou PNG.",
            'cover_image.max' => "L'image de couverture ne peut pas dépasser 5 Mo.",
        ];
    }

    protected function canBypassPublicationPayment(Request $request): bool
    {
        return (bool) $request->user()?->hasRole('admin');
    }

    protected function renderContributorPage(string $activeTab, string $contentView, array $data = []): Response
    {
        $content = render_php_view($contentView, $data);
        $wrapper = render_php_view('site.contributor.wrapper', [
            'activeTab' => $activeTab,
            'contributorContent' => $content,
            'pending_quotes_count' => $data['pending_quotes_count'] ?? 0,
        ]);
        $html = render_php_view('site.layout', [
            'content' => $wrapper,
            'content_locale' => content_locale(request()),
            'title' => 'Espace rédacteur Vivat',
            'meta_description' => 'Espace rédacteur Vivat. Gérez vos soumissions et rédigez des articles.',
            'hide_cta_section' => true,
            'hide_footer' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    protected function contributorSubmissionStatusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'submitted' => 'En vérification',
            'under_review' => 'En relecture',
            'changes_requested' => 'Corrections demandées',
            'price_proposed', 'awaiting_payment' => 'Paiement requis',
            'payment_pending' => 'Paiement en cours',
            'payment_succeeded', 'approved', 'published' => 'Publié',
            'payment_failed' => 'Paiement échoué',
            'payment_expired' => 'Offre expirée',
            'rejected' => 'Refusé',
            default => 'Aucune',
        };
    }

    protected function contributorPaymentStatusMeta(Payment $payment): array
    {
        $submissionStatus = $payment->submission?->status;

        if ($payment->status === 'paid') {
            return [
                'label' => 'Payé',
                'color' => 'emerald',
                'description' => match ($submissionStatus) {
                    'pending' => 'Paiement confirmé. Votre article est en cours de relecture.',
                    'approved', 'published', 'payment_succeeded' => 'Paiement confirmé. Votre article est publié.',
                    'rejected' => "Paiement confirmé. L'article a été refusé.",
                    default => 'Paiement confirmé.',
                },
            ];
        }

        if ($payment->status === 'refunded') {
            return [
                'label' => 'Remboursé',
                'color' => 'sky',
                'description' => 'Le remboursement a été confirmé et un reçu est disponible.',
            ];
        }

        if ($payment->status === 'failed') {
            return [
                'label' => 'Échoué',
                'color' => 'rose',
                'description' => 'Le paiement a échoué. Vous pouvez reprendre la soumission et réessayer.',
            ];
        }

        if ($payment->status === 'abandoned') {
            return [
                'label' => 'Abandonné',
                'color' => 'slate',
                'description' => 'Cette tentative a expiré ou a été remplacée par un nouveau paiement.',
            ];
        }

        return [
            'label' => $submissionStatus === 'draft' ? 'Interrompu' : 'En attente',
            'color' => $submissionStatus === 'draft' ? 'amber' : 'slate',
            'description' => $submissionStatus === 'draft'
                ? "Un paiement a été initié, mais il n'a pas été finalisé."
                : "Le paiement existe, mais Stripe ne l'a pas encore confirmé.",
        ];
    }

    protected function contributorCategories(): array
    {
        return Category::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($category) => ['id' => $category->id, 'name' => $category->name])
            ->all();
    }

    protected function deleteLocalSubmissionCover(?string $coverImageUrl): void
    {
        if (! is_string($coverImageUrl) || ! str_starts_with($coverImageUrl, '/uploads/submissions/')) {
            return;
        }

        $coverPath = public_path(ltrim($coverImageUrl, '/'));

        if (File::exists($coverPath)) {
            File::delete($coverPath);
        }
    }
}
