<?php

namespace App\Http\Controllers\Web;

use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContributorSubmissionController extends ContributorBaseController
{
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

            $status = $shouldSubmit && $canBypassPayment ? 'pending' : ($shouldSubmit ? 'submitted' : 'draft');
            $coverImageUrl = $submission->cover_image_url;

            if ($request->hasFile('cover_image')) {
                $this->deleteLocalSubmissionCover($submission->cover_image_url);
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
                'status' => $status === 'submitted' ? $submission->status : $status,
                'cover_image_url' => $coverImageUrl,
                'reviewer_notes' => $status === 'pending' ? null : $submission->reviewer_notes,
                'reviewed_by' => $status === 'pending' ? null : $submission->reviewed_by,
                'reviewed_at' => $status === 'pending' ? null : $submission->reviewed_at,
            ]);

            if ($status === 'submitted') {
                $this->submissionWorkflow->submit($submission->fresh(), $request->user());
                $submission->refresh();
            }

            if ($isAutosave) {
                return response()->json($this->autosavePayload($submission->fresh(['category', 'reviewer'])));
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

                    return response()->json($this->adminPublishedPayload(url('/articles/'.$article->slug)));
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
                    ->to(url('/articles/'.$article->slug))
                    ->with('success', 'Article publié directement.');
            }

            if ($shouldSubmit) {
                return redirect()
                    ->route('contributor.dashboard')
                    ->with('success', 'Votre article a été envoyé en vérification. Vous serez notifié lorsqu\'un prix vous sera proposé.');
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
        $errors = $request->session()->get('errors');

        return $this->renderContributorPage('new', 'site.contributor.new', [
            'categories' => $this->contributorCategories(),
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
            $safeTitle = $nextTitle !== '' ? $nextTitle : 'Brouillon '.now()->format('d/m H:i');

            $submission = Submission::create([
                'user_id' => $request->user()->id,
                'title' => $safeTitle,
                'excerpt' => $validated['excerpt'] ?? null,
                'content' => $nextContent,
                'category_id' => $validated['category_id'] ?? null,
                'language' => $validated['language'] ?? 'fr',
                'reading_time' => $validated['reading_time'] ?? 5,
                'status' => $status,
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
                        return response()->json($this->adminPublishedPayload(url('/articles/'.$article->slug)));
                    }

                    return redirect()
                        ->to(url('/articles/'.$article->slug))
                        ->with('success', 'Article publié directement.');
                }

                $this->submissionWorkflow->submit($submission->fresh(), $request->user());

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json($this->submissionAcceptedPayload(route('contributor.dashboard')));
                }

                return redirect()
                    ->route('contributor.dashboard')
                    ->with('success', 'Votre article a été envoyé en vérification. Vous serez notifié lorsqu\'un prix vous sera proposé.');
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

        $errors = $request->session()->get('errors');
        $old = $request->old();

        return $this->renderContributorPage('new', 'site.contributor.new', [
            'categories' => $this->contributorCategories(),
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
            'submission' => null,
            'form_action' => url('/contributor/new'),
            'is_editing' => false,
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
            'title' => $submission->title.' Preview Vivat',
            'meta_description' => $submission->excerpt ?: 'Prévisualisation de votre article Vivat.',
            'canonical_url' => route('contributor.articles.show', ['submission' => $submission->slug]),
            'og_image' => $submission->cover_image_url
                ? (str_starts_with($submission->cover_image_url, '/') ? url($submission->cover_image_url) : $submission->cover_image_url)
                : null,
            'og_article' => true,
            'hide_cta_section' => true,
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

        $this->deleteLocalSubmissionCover($submission->cover_image_url);
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
                ->with('error', "Seul un article publié peut faire l'objet d'une demande de dépublication.");
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
}
