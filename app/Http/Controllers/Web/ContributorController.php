<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Submission;
use App\Services\AccountDeletionService;
use App\Services\SubmissionPublishingService;
use App\Services\SubmissionImageStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ContributorController extends Controller
{
    public function __construct(
        private readonly AccountDeletionService $accountDeletionService,
        private readonly SubmissionPublishingService $submissionPublishingService,
        private readonly SubmissionImageStorageService $submissionImageStorage,
    ) {}

    private function publicationPrice(): int
    {
        return (int) config('services.stripe.publication_price', 1500);
    }

    private function hasPaidPublication(Submission $submission): bool
    {
        return Payment::query()
            ->where('submission_id', $submission->id)
            ->where('status', 'paid')
            ->exists();
    }

    private function paymentRequiredPayload(Submission $submission): array
    {
        return [
            'ok' => true,
            'submission_id' => $submission->id,
            'status' => 'draft',
            'requires_payment' => true,
            'redirect_url' => route('contributor.dashboard'),
            'notice' => [
                'title' => 'Paiement requis',
                'message' => 'Votre article est enregistre comme brouillon. Finalisez maintenant le paiement pour l’envoyer en validation.',
            ],
        ];
    }

    private function submissionAcceptedPayload(string $redirectUrl): array
    {
        return [
            'ok' => true,
            'status' => 'pending',
            'redirect_url' => $redirectUrl,
            'notice' => [
                'title' => 'Article transmis',
                'message' => 'Votre article va etre verifie par notre equipe et sera publie automatiquement apres acceptation.',
            ],
        ];
    }

    private function adminPublishedPayload(string $redirectUrl): array
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

    private function isAutosaveRequest(Request $request): bool
    {
        return $request->boolean('autosave') || $request->header('X-Autosave') === '1';
    }

    private function generateUniqueSubmissionSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug(trim($title)) ?: 'brouillon';
        $slug = $base;
        $suffix = 2;

        while (Submission::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function autosavePayload(Submission $submission): array
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

    private function shouldForkRejectedSubmission(Submission $submission): bool
    {
        return $submission->status === 'rejected'
            && (
                filled($submission->payment_id)
                || filled($submission->reviewer_notes)
                || filled($submission->reviewed_by)
                || filled($submission->reviewed_at)
            );
    }

    private function findExistingRevisionDraft(Submission $submission, string $userId): ?Submission
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

    private function createRevisionDraftFromRejected(Submission $submission, string $userId): Submission
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

    private function createRevisionDraftFromPublished(Submission $submission, string $userId): Submission
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

    private function contributorSubmissionRules(bool $isAutosave, bool $isSubmitting): array
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

    private function contributorSubmissionMessages(): array
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
            'reading_time.min' => 'Le temps de lecture doit être d’au moins 1 minute.',
            'reading_time.max' => 'Le temps de lecture ne peut pas dépasser 120 minutes.',
            'cover_image.image' => "L'image de couverture doit être une image valide.",
            'cover_image.mimes' => "L'image de couverture doit être au format JPG ou PNG.",
            'cover_image.max' => "L'image de couverture ne peut pas dépasser 5 Mo.",
        ];
    }

    private function canBypassPublicationPayment(Request $request): bool
    {
        return (bool) $request->user()?->hasRole('admin');
    }

    private function renderContributorPage(string $activeTab, string $contentView, array $data = []): Response
    {
        $content = render_php_view($contentView, $data);
        $wrapper = render_php_view('site.contributor.wrapper', [
            'activeTab' => $activeTab,
            'contributorContent' => $content,
        ]);
        $html = render_php_view('site.layout', [
            'content' => $wrapper,
            'content_locale' => content_locale(request()),
            'title' => 'Espace rédacteur — Vivat',
            'meta_description' => 'Espace rédacteur Vivat. Gérez vos soumissions et rédigez des articles.',
            'hide_cta_section' => true,
            'hide_footer' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function contributorSubmissionStatusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'approved' => 'Publié',
            'rejected' => 'Refusé',
            default => 'Aucune',
        };
    }

    /**
     * @return array{label:string,color:string,description:string}
     */
    private function contributorPaymentStatusMeta(Payment $payment): array
    {
        $submissionStatus = $payment->submission?->status;

        if ($payment->status === 'paid') {
            return [
                'label' => 'Payé',
                'color' => 'emerald',
                'description' => match ($submissionStatus) {
                    'pending' => 'Paiement confirmé. Votre article est en cours de relecture.',
                    'approved' => 'Paiement confirmé. Votre article est publié.',
                    'rejected' => 'Paiement confirmé. L’article a été refusé.',
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
                ? 'Un paiement a été initié, mais il n’a pas été finalisé.'
                : 'Le paiement existe, mais Stripe ne l’a pas encore confirmé.',
        ];
    }

    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        $submissionsPaginator = Submission::where('user_id', $user->id)
            ->with(['category', 'reviewer', 'payment', 'publishedArticle'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $submissions = $submissionsPaginator->getCollection()
            ->map(function ($s) {
                $payment = $s->payment;

                return [
                'id' => $s->id,
                'title' => $s->title,
                'slug' => $s->slug,
                'status' => $s->status,
                'status_label' => match ($s->status) {
                    'draft' => 'Brouillon',
                    'pending' => 'En attente',
                    'approved' => 'Approuvé',
                    'rejected' => 'Rejeté',
                    default => ucfirst((string) $s->status),
                },
                'created_at' => $s->created_at?->format('d/m/Y'),
                'reading_time' => $s->reading_time,
                'cover_image_url' => $s->cover_image_url,
                'excerpt' => $s->excerpt,
                'reviewer_notes' => $s->reviewer_notes,
                'reviewed_at' => $s->reviewed_at?->format('d/m/Y H:i'),
                'reviewer_name' => $s->reviewer?->name,
                'category' => $s->category ? ['name' => $s->category->name] : null,
                'payment_status' => $payment?->status,
                'payment_amount' => $payment?->amount,
                'payment_amount_label' => $payment
                    ? number_format($payment->amount / 100, 2, ',', ' ') . ' ' . strtoupper($payment->currency ?: 'EUR')
                    : null,
                'language' => $s->language ?? 'fr',
                'refund_reason' => $payment?->refund_reason,
                'refunded_at' => $payment?->status === 'refunded' ? $payment->updated_at?->format('d/m/Y H:i') : null,
                'refund_receipt_url' => $payment?->status === 'refunded'
                    ? route('contributor.payments.refund-receipt', ['payment' => $payment->id])
                    : null,
                'preview_url' => route('contributor.articles.show', ['submission' => $s->slug]),
                'published_article_url' => $s->publishedArticle?->slug ? url('/articles/' . $s->publishedArticle->slug) : null,
                'depublication_requested_at' => $s->depublication_requested_at?->format('d/m/Y H:i'),
                'edit_url' => route('contributor.articles.edit', ['submission' => $s->slug]),
                'can_delete' => $s->status === 'draft' || $s->status === 'rejected',
                'delete_url' => ($s->status === 'draft' || $s->status === 'rejected')
                    ? route('contributor.articles.destroy', ['submission' => $s->slug])
                    : null,
                'request_unpublish_url' => $s->status === 'approved' && $s->published_article_id
                    ? route('contributor.articles.request-unpublish', ['submission' => $s->slug])
                    : null,
                ];
            })
            ->all();

        $submissionsPaginator->setCollection(collect($submissions));

        return $this->renderContributorPage('articles', 'site.contributor.articles', [
            'user' => $user,
            'submissions' => $submissionsPaginator->items(),
            'pagination' => $submissionsPaginator,
        ]);
    }

    public function refundReceipt(Request $request, Payment $payment): Response
    {
        abort_unless(
            $request->user()
                && ($request->user()->id === $payment->user_id || $request->user()->hasRole('admin')),
            403
        );

        abort_unless($payment->status === 'refunded', 404);

        $payment->loadMissing('submission.category');

        return $this->renderContributorPage('payments', 'site.contributor.refund_receipt', [
            'payment' => $payment,
            'submission' => $payment->submission,
        ]);
    }

    public function paymentsHistory(Request $request): Response
    {
        $user = $request->user();
        $paymentsPaginator = Payment::query()
            ->where('user_id', $user->id)
            ->with(['submission.category', 'submission.publishedArticle'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $payments = $paymentsPaginator->getCollection()
            ->map(function (Payment $payment) {
                $submission = $payment->submission;
                $statusMeta = $this->contributorPaymentStatusMeta($payment);

                return [
                    'id' => $payment->id,
                    'title' => $submission?->title ?: 'Paiement sans soumission active',
                    'amount_label' => number_format($payment->amount / 100, 2, ',', ' ') . ' ' . strtoupper($payment->currency ?: 'EUR'),
                    'status' => $payment->status,
                    'status_label' => $statusMeta['label'],
                    'status_color' => $statusMeta['color'],
                    'status_description' => $statusMeta['description'],
                    'created_at' => $payment->created_at?->format('d/m/Y à H:i'),
                    'submission_status_label' => $this->contributorSubmissionStatusLabel($submission?->status),
                    'category_name' => $submission?->category?->name,
                    'refund_reason' => $payment->refund_reason,
                    'refund_receipt_url' => $payment->status === 'refunded'
                        ? route('contributor.payments.refund-receipt', ['payment' => $payment->id])
                        : null,
                    'submission_preview_url' => $submission?->slug
                        ? route('contributor.articles.show', ['submission' => $submission->slug])
                        : null,
                    'submission_edit_url' => $submission?->slug
                        ? route('contributor.articles.edit', ['submission' => $submission->slug])
                        : null,
                    'published_article_url' => $submission?->publishedArticle?->slug
                        ? url('/articles/' . $submission->publishedArticle->slug)
                        : null,
                ];
            })
            ->all();

        $paymentsPaginator->setCollection(collect($payments));

        return $this->renderContributorPage('payments', 'site.contributor.payments', [
            'user' => $user,
            'payments' => $paymentsPaginator->items(),
            'pagination' => $paymentsPaginator,
        ]);
    }

    public function editSubmission(Request $request, Submission $submission): Response|RedirectResponse|JsonResponse
    {
        abort_unless(
            $request->user()
                && ($request->user()->id === $submission->user_id || $request->user()->hasRole('admin')),
            403
        );

        if ($submission->status === 'approved') {
            $existingRevision = $this->findExistingRevisionDraft($submission, $request->user()->id);

            if ($existingRevision) {
                return redirect()->route('contributor.articles.edit', ['submission' => $existingRevision->slug]);
            }

            $revisionDraft = $this->createRevisionDraftFromPublished($submission, $request->user()->id);

            return redirect()
                ->route('contributor.articles.edit', ['submission' => $revisionDraft->slug])
                ->with('info', 'Une nouvelle version brouillon a ete creee pour modifier gratuitement cet article publie.');
        }

        if ($request->isMethod('post')) {
            $isAutosave = $this->isAutosaveRequest($request);
            $shouldSubmit = ! $isAutosave && $request->input('status') === 'submitted';
            $canBypassPayment = $this->canBypassPublicationPayment($request);
            $validated = $request->validate(
                $this->contributorSubmissionRules($isAutosave, $shouldSubmit),
                $this->contributorSubmissionMessages()
            );

            if ($this->shouldForkRejectedSubmission($submission)) {
                $submission = $this->createRevisionDraftFromRejected($submission, $request->user()->id);
            }

            $hasFreePublishedRevision = filled($submission->published_article_id);
            $status = $shouldSubmit && ($this->hasPaidPublication($submission) || $hasFreePublishedRevision || $canBypassPayment) ? 'pending' : 'draft';
            $coverImageUrl = $submission->cover_image_url;

            if ($request->hasFile('cover_image')) {
                if (is_string($submission->cover_image_url) && str_starts_with($submission->cover_image_url, '/uploads/submissions/')) {
                    $existingCoverPath = public_path(ltrim($submission->cover_image_url, '/'));

                    if (File::exists($existingCoverPath)) {
                        File::delete($existingCoverPath);
                    }
                }

                $coverImageUrl = $this->submissionImageStorage->storeUploadedImage($request->file('cover_image'));
            } elseif (is_string($coverImageUrl) && $coverImageUrl !== '') {
                $coverImageUrl = $this->submissionImageStorage->migrateLocalImageUrl($coverImageUrl);
            }

            $nextTitle = array_key_exists('title', $validated)
                ? trim((string) ($validated['title'] ?? ''))
                : $submission->title;
            $nextTitle = $nextTitle !== '' ? $nextTitle : $submission->title;
            $nextContent = array_key_exists('content', $validated)
                ? (string) ($validated['content'] ?? '')
                : $submission->content;
            $nextSlug = $submission->slug;

            if ($nextTitle !== '' && $nextTitle !== $submission->title) {
                $nextSlug = $this->generateUniqueSubmissionSlug($nextTitle, $submission->id);
            }

            $submission->update([
                'title' => $nextTitle,
                'slug' => $nextSlug,
                'excerpt' => $validated['excerpt'] ?? null,
                'content' => $nextContent,
                'category_id' => $validated['category_id'] ?? null,
                'language' => $validated['language'] ?? ($submission->language ?? 'fr'),
                'reading_time' => $validated['reading_time'] ?? 5,
                'status' => $status,
                'cover_image_url' => $coverImageUrl,
                'reviewer_notes' => $status === 'pending' ? null : $submission->reviewer_notes,
                'reviewed_by' => $status === 'pending' ? null : $submission->reviewed_by,
                'reviewed_at' => $status === 'pending' ? null : $submission->reviewed_at,
            ]);

            if ($isAutosave) {
                return response()->json($this->autosavePayload($submission->fresh(['category', 'reviewer'])));
            }

            if ($shouldSubmit && $status !== 'pending') {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json($this->paymentRequiredPayload($submission));
                }

                return redirect()
                    ->route('contributor.articles.edit', ['submission' => $submission->slug])
                    ->with('info', 'Le paiement Stripe est requis avant envoi en validation.');
            }

            if ($shouldSubmit && ($request->expectsJson() || $request->ajax())) {
                if ($canBypassPayment) {
                    $article = $this->submissionPublishingService->approveAndPublish(
                        $submission->fresh(),
                        [
                            'category_id' => $submission->category_id,
                            'article_type' => 'standard',
                            'reviewer_notes' => 'Publication directe par un administrateur.',
                        ],
                        $request->user()
                    );

                    return response()->json(
                        $this->adminPublishedPayload(url('/articles/' . $article->slug))
                    );
                }

                return response()->json($this->submissionAcceptedPayload(route('contributor.dashboard')));
            }

            if ($shouldSubmit && $canBypassPayment) {
                $article = $this->submissionPublishingService->approveAndPublish(
                    $submission->fresh(),
                    [
                        'category_id' => $submission->category_id,
                        'article_type' => 'standard',
                        'reviewer_notes' => 'Publication directe par un administrateur.',
                    ],
                    $request->user()
                );

                return redirect()
                    ->to(url('/articles/' . $article->slug))
                    ->with('success', 'Article publié directement.');
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'submission_id' => $submission->id,
                    'status' => $status,
                    'slug' => $submission->slug,
                    'edit_url' => route('contributor.articles.edit', ['submission' => $submission->slug]),
                    'preview_url' => route('contributor.articles.show', ['submission' => $submission->slug]),
                    'redirect_url' => null,
                    'notice' => [
                        'title' => 'Brouillon enregistré',
                        'message' => 'Votre brouillon a bien été mis à jour.',
                    ],
                ]);
            }

            return redirect()
                ->route('contributor.dashboard')
                ->with('success', $status === 'pending'
                    ? 'Article mis à jour et renvoyé en validation.'
                    : 'Brouillon mis à jour.');
        }

        $submission->loadMissing('reviewer');
        $categories = Category::orderBy('name')->get(['id', 'name'])->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all();
        $errors = $request->session()->get('errors');

        return $this->renderContributorPage('new', 'site.contributor.new', [
            'categories' => $categories,
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => [
                'title' => old('title', $submission->title),
                'excerpt' => old('excerpt', $submission->excerpt),
                'content' => old('content', $submission->content),
                'category_id' => old('category_id', $submission->category_id),
                'language' => old('language', $submission->language ?? 'fr'),
                'reading_time' => old('reading_time', $submission->reading_time ?: 5),
            ],
            'submission' => [
                'id' => $submission->id,
                'title' => $submission->title,
                'status' => $submission->status,
                'is_paid' => $this->hasPaidPublication($submission),
                'language' => $submission->language ?? 'fr',
                'reviewer_notes' => $submission->reviewer_notes,
                'reviewed_at' => $submission->reviewed_at?->format('d/m/Y H:i'),
                'reviewer_name' => $submission->reviewer?->name,
                'cover_image_url' => $submission->cover_image_url,
            ],
            'form_action' => route('contributor.articles.edit', ['submission' => $submission->slug]),
            'is_editing' => true,
            'stripe_key' => (string) config('services.stripe.key'),
            'publication_price' => $this->publicationPrice(),
            'payment_create_url' => route('contributor.web-payments.create-intent'),
            'payment_confirm_url' => route('contributor.web-payments.confirm'),
            'can_bypass_payment' => $this->canBypassPublicationPayment($request),
        ]);
    }

    public function newArticle(Request $request): Response|RedirectResponse|JsonResponse
    {
        if ($request->isMethod('post')) {
            $isAutosave = $this->isAutosaveRequest($request);
            $shouldSubmit = ! $isAutosave && $request->input('status') === 'submitted';
            $canBypassPayment = $this->canBypassPublicationPayment($request);
            $validated = $request->validate(
                $this->contributorSubmissionRules($isAutosave, $shouldSubmit),
                $this->contributorSubmissionMessages()
            );

            $status = $shouldSubmit && $canBypassPayment ? 'pending' : 'draft';
            $coverImageUrl = $request->hasFile('cover_image')
                ? $this->submissionImageStorage->storeUploadedImage($request->file('cover_image'))
                : null;
            $nextTitle = trim((string) ($validated['title'] ?? ''));
            $nextContent = (string) ($validated['content'] ?? '');
            $safeTitle = $nextTitle !== '' ? $nextTitle : 'Brouillon ' . now()->format('d/m H:i');

            $submission = Submission::create([
                'user_id'     => $request->user()->id,
                'title'       => $safeTitle,
                'excerpt'     => $validated['excerpt'] ?? null,
                'content'     => $nextContent,
                'category_id' => $validated['category_id'] ?? null,
                'language' => $validated['language'] ?? 'fr',
                'reading_time' => $validated['reading_time'] ?? 5,
                'status'      => $status,
                'cover_image_url' => $coverImageUrl,
            ]);

            if ($isAutosave) {
                return response()->json($this->autosavePayload($submission));
            }

            if ($shouldSubmit) {
                if ($canBypassPayment) {
                    $article = $this->submissionPublishingService->approveAndPublish(
                        $submission->fresh(),
                        [
                            'category_id' => $submission->category_id,
                            'article_type' => 'standard',
                            'reviewer_notes' => 'Publication directe par un administrateur.',
                        ],
                        $request->user()
                    );

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(
                            $this->adminPublishedPayload(url('/articles/' . $article->slug))
                        );
                    }

                    return redirect()
                        ->to(url('/articles/' . $article->slug))
                        ->with('success', 'Article publié directement.');
                }

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json($this->paymentRequiredPayload($submission));
                }

                return redirect()
                    ->route('contributor.articles.edit', ['submission' => $submission->slug])
                    ->with('info', 'Le paiement Stripe est requis avant envoi en validation.');
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'submission_id' => $submission->id,
                    'status' => $status,
                    'redirect_url' => route('contributor.dashboard'),
                    'notice' => $status === 'pending'
                        ? [
                            'title' => 'Article transmis',
                            'message' => 'Votre article va être vérifié par notre équipe et sera publié automatiquement après acceptation.',
                        ]
                        : [
                            'title' => 'Brouillon enregistré',
                            'message' => 'Votre brouillon a été enregistré dans votre espace rédacteur.',
                        ],
                ]);
            }

            $redirect = redirect()->route('contributor.dashboard')
                ->with('success', $status === 'pending' ? 'Article soumis avec succès !' : 'Brouillon enregistré.');

            if ($status === 'pending') {
                $redirect->with('submission_notice', [
                    'title' => 'Article transmis',
                    'message' => 'Votre article va être vérifié par notre équipe et sera publié automatiquement après acceptation.',
                ]);
            }

            return $redirect;
        }

        $categories = Category::orderBy('name')->get(['id', 'name'])->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all();
        $errors = $request->session()->get('errors');
        $old = $request->old();

        return $this->renderContributorPage('new', 'site.contributor.new', [
            'categories' => $categories,
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
            'submission' => null,
            'form_action' => url('/contributor/new'),
            'is_editing' => false,
            'stripe_key' => (string) config('services.stripe.key'),
            'publication_price' => $this->publicationPrice(),
            'payment_create_url' => route('contributor.web-payments.create-intent'),
            'payment_confirm_url' => route('contributor.web-payments.confirm'),
            'can_bypass_payment' => $this->canBypassPublicationPayment($request),
        ]);
    }

    public function showSubmission(Request $request, Submission $submission): Response
    {
        abort_unless(
            $request->user()
                && ($request->user()->id === $submission->user_id || $request->user()->hasRole('admin')),
            403
        );

        $submission->load('category');
        $category = $submission->category;

        $data = [
            'article' => [
                'id' => $submission->id,
                'title' => $submission->title,
                'slug' => $submission->slug,
                'excerpt' => $submission->excerpt,
                'content' => $submission->content,
                'meta_title' => $submission->title,
                'meta_description' => $submission->excerpt,
                'reading_time' => $submission->reading_time,
                'published_at' => $submission->created_at?->format('d/m/Y H:i'),
                'published_at_display' => $submission->created_at?->locale('fr')->isoFormat('D MMMM YYYY'),
                'published_at_iso' => $submission->created_at?->toIso8601String(),
                'cover_image_url' => $submission->cover_image_url,
                'cover_video_url' => null,
                'is_preview' => true,
                'category' => $category ? [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ] : null,
            ],
        ];

        $content = render_php_view('site.article', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => content_locale($request),
            'title' => $submission->title . ' — Preview Vivat',
            'meta_description' => $submission->excerpt ?: 'Prévisualisation de votre article Vivat.',
            'canonical_url' => route('contributor.articles.show', ['submission' => $submission->slug]),
            'og_image' => $submission->cover_image_url
                ? (str_starts_with($submission->cover_image_url, '/') ? url($submission->cover_image_url) : $submission->cover_image_url)
                : null,
            'og_article' => true,
            'hide_footer' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function destroySubmission(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless(
            $request->user()
                && ($request->user()->id === $submission->user_id || $request->user()->hasRole('admin')),
            403
        );

        if ($submission->status === 'pending' || $submission->status === 'approved') {
            return redirect()
                ->route('contributor.dashboard')
                ->with('error', 'Cet article ne peut plus être supprimé car il est déjà en relecture ou publié.');
        }

        if ($this->hasPaidPublication($submission)) {
            return redirect()
                ->route('contributor.dashboard')
                ->with('error', 'Cet article ne peut pas être supprimé car un paiement y est déjà lié.');
        }

        if (is_string($submission->cover_image_url) && str_starts_with($submission->cover_image_url, '/uploads/submissions/')) {
            $coverPath = public_path(ltrim($submission->cover_image_url, '/'));

            if (File::exists($coverPath)) {
                File::delete($coverPath);
            }
        }

        $submission->delete();

        return redirect()
            ->route('contributor.dashboard')
            ->with('success', 'Article supprimé.');
    }

    public function requestUnpublish(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless(
            $request->user()
                && ($request->user()->id === $submission->user_id || $request->user()->hasRole('admin')),
            403
        );

        if ($submission->status !== 'approved' || ! $submission->published_article_id) {
            return redirect()
                ->route('contributor.dashboard')
                ->with('error', 'Seul un article publié peut faire l’objet d’une demande de dépublication.');
        }

        if ($submission->depublication_requested_at) {
            return redirect()
                ->route('contributor.dashboard')
                ->with('info', 'Une demande de dépublication a déjà été envoyée pour cet article.');
        }

        $submission->update([
            'depublication_requested_at' => now(),
            'depublication_reason' => 'Demande du rédacteur depuis son espace personnel.',
        ]);

        return redirect()
            ->route('contributor.dashboard')
            ->with('success', 'Votre demande de dépublication a bien été envoyée à la rédaction.');
    }

    public function profile(Request $request): Response|RedirectResponse
    {
        if ($request->isMethod('post')) {
            if ($request->input('form_type') === 'delete_account') {
                $user = $request->user();

                if ($user->hasRole('admin')) {
                    return redirect()
                        ->route('contributor.profile')
                        ->withErrors(['delete_account' => 'La suppression automatique d’un compte administrateur est bloquée pour préserver l’accès au back-office.'])
                        ->withInput();
                }

                $rules = [
                    'delete_email' => ['required', 'email'],
                    'delete_confirmation' => ['accepted'],
                ];

                if (blank($user->google_id)) {
                    $rules['current_password_delete'] = ['required', 'current_password'];
                }

                $validated = $request->validate($rules, [
                    'delete_email.required' => 'Veuillez confirmer votre adresse email.',
                    'delete_email.email' => 'Veuillez entrer une adresse email valide.',
                    'delete_confirmation.accepted' => 'Vous devez confirmer la suppression définitive du compte.',
                    'current_password_delete.required' => 'Votre mot de passe actuel est obligatoire pour supprimer le compte.',
                    'current_password_delete.current_password' => 'Le mot de passe actuel est incorrect.',
                ]);

                if (! hash_equals((string) $user->email, (string) $validated['delete_email'])) {
                    return redirect()
                        ->route('contributor.profile')
                        ->withErrors(['delete_email' => 'L’adresse email de confirmation ne correspond pas à votre compte.'])
                        ->withInput();
                }

                $this->accountDeletionService->anonymize($user);

                Auth::guard('web')->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('home')
                    ->with('success', 'Votre compte a été supprimé et vos données personnelles ont été anonymisées.');
            }

            if ($request->input('form_type') === 'password') {
                $validated = $request->validate([
                    'current_password' => ['required', 'current_password'],
                    'password' => [
                        'required',
                        'confirmed',
                        \Illuminate\Validation\Rules\Password::min(12)
                            ->mixedCase()
                            ->numbers()
                            ->symbols(),
                    ],
                ], [
                    'current_password.required' => 'Votre mot de passe actuel est obligatoire.',
                    'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
                    'password.required' => 'Le nouveau mot de passe est obligatoire.',
                    'password.confirmed' => 'Les nouveaux mots de passe ne correspondent pas.',
                    'password.min' => 'Le nouveau mot de passe doit contenir au moins 12 caractères.',
                    'password.mixed' => 'Le nouveau mot de passe doit contenir une majuscule et une minuscule.',
                    'password.numbers' => 'Le nouveau mot de passe doit contenir au moins un chiffre.',
                    'password.symbols' => 'Le nouveau mot de passe doit contenir au moins un symbole.',
                ]);

                $request->user()->forceFill([
                    'password' => $validated['password'],
                ])->save();

                return redirect()->route('contributor.profile')->with('success', 'Mot de passe mis à jour.');
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'bio' => ['nullable', 'string', 'max:2000'],
                'instagram_url' => ['nullable', 'url', 'max:255'],
                'twitter_url' => ['nullable', 'url', 'max:255'],
                'website_url' => ['nullable', 'url', 'max:255'],
            ], [
                'name.required' => 'Le nom complet est obligatoire.',
                'name.max' => 'Le nom complet ne peut pas dépasser 255 caractères.',
                'bio.max' => 'La biographie ne peut pas dépasser 2000 caractères.',
                'instagram_url.url' => "Le lien Instagram doit etre une URL valide.",
                'instagram_url.max' => "Le lien Instagram est trop long.",
                'twitter_url.url' => "Le lien Twitter doit etre une URL valide.",
                'twitter_url.max' => "Le lien Twitter est trop long.",
                'website_url.url' => "Le site web doit etre une URL valide.",
                'website_url.max' => "Le site web est trop long.",
            ]);

            $request->user()->update([
                'name' => $validated['name'],
                'bio' => $validated['bio'] ?? null,
                'instagram_url' => $validated['instagram_url'] ?? null,
                'twitter_url' => $validated['twitter_url'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
            ]);

            return redirect()->route('contributor.profile')->with('success', 'Profil mis à jour.');
        }

        $errors = $request->session()->get('errors');
        $old = $request->old();

        return $this->renderContributorPage('profile', 'site.contributor.profile', [
            'user' => $request->user(),
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
        ]);
    }

}
