<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Submission;
use App\Services\SubmissionImageStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class ContributorController extends Controller
{
    public function __construct(
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

    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        $submissions = Submission::where('user_id', $user->id)
            ->with(['category', 'reviewer'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($s) => [
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
                'preview_url' => route('contributor.articles.show', ['submission' => $s->slug]),
                'edit_url' => $s->status === 'approved'
                    ? null
                    : route('contributor.articles.edit', ['submission' => $s->slug]),
                'delete_url' => route('contributor.articles.destroy', ['submission' => $s->slug]),
            ])
            ->all();

        return $this->renderContributorPage('articles', 'site.contributor.articles', [
            'user' => $user,
            'submissions' => $submissions,
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
            return redirect()
                ->route('contributor.dashboard')
                ->with('info', 'Cet article a deja ete approuve et ne peut plus etre modifie.');
        }

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'excerpt' => ['nullable', 'string', 'max:500'],
                'content' => ['required', 'string', 'min:100'],
                'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
                'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
                'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'status' => ['sometimes', 'in:draft,submitted'],
            ], [
                'title.required' => 'Le titre est obligatoire.',
                'content.required' => 'Le contenu est obligatoire.',
                'content.min' => 'Le contenu doit contenir au moins 100 caractères.',
                'cover_image.image' => "L'image de couverture doit etre une image valide.",
                'cover_image.max' => "L'image de couverture ne peut pas depasser 5 Mo.",
            ]);

            $shouldSubmit = $request->input('status') === 'submitted';
            $status = $shouldSubmit && $this->hasPaidPublication($submission) ? 'pending' : 'draft';
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

            $submission->update([
                'title' => $validated['title'],
                'excerpt' => $validated['excerpt'] ?? null,
                'content' => $validated['content'],
                'category_id' => $validated['category_id'] ?? null,
                'reading_time' => $validated['reading_time'] ?? 5,
                'status' => $status,
                'cover_image_url' => $coverImageUrl,
                'reviewer_notes' => $status === 'pending' ? null : $submission->reviewer_notes,
                'reviewed_by' => $status === 'pending' ? null : $submission->reviewed_by,
                'reviewed_at' => $status === 'pending' ? null : $submission->reviewed_at,
            ]);

            if ($shouldSubmit && $status !== 'pending') {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json($this->paymentRequiredPayload($submission));
                }

                return redirect()
                    ->route('contributor.articles.edit', ['submission' => $submission->slug])
                    ->with('info', 'Le paiement Stripe est requis avant envoi en validation.');
            }

            if ($shouldSubmit && ($request->expectsJson() || $request->ajax())) {
                return response()->json($this->submissionAcceptedPayload(route('contributor.dashboard')));
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
                'reading_time' => old('reading_time', $submission->reading_time ?: 5),
            ],
            'submission' => [
                'id' => $submission->id,
                'title' => $submission->title,
                'status' => $submission->status,
                'is_paid' => $this->hasPaidPublication($submission),
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
        ]);
    }

    public function newArticle(Request $request): Response|RedirectResponse|JsonResponse
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'title'       => ['required', 'string', 'max:255'],
                'excerpt'     => ['nullable', 'string', 'max:500'],
                'content'     => ['required', 'string', 'min:100'],
                'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
                'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
                'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'status'      => ['sometimes', 'in:draft,submitted'],
            ], [
                'title.required' => 'Le titre est obligatoire.',
                'content.required' => 'Le contenu est obligatoire.',
                'content.min' => 'Le contenu doit contenir au moins 100 caractères.',
                'cover_image.image' => "L'image de couverture doit etre une image valide.",
                'cover_image.max' => "L'image de couverture ne peut pas depasser 5 Mo.",
            ]);

            $shouldSubmit = $request->input('status') === 'submitted';
            $status = 'draft';
            $coverImageUrl = $request->hasFile('cover_image')
                ? $this->submissionImageStorage->storeUploadedImage($request->file('cover_image'))
                : null;

            $submission = Submission::create([
                'user_id'     => $request->user()->id,
                'title'       => $validated['title'],
                'excerpt'     => $validated['excerpt'] ?? null,
                'content'     => $validated['content'],
                'category_id' => $validated['category_id'] ?? null,
                'reading_time' => $validated['reading_time'] ?? 5,
                'status'      => $status,
                'cover_image_url' => $coverImageUrl,
            ]);

            if ($shouldSubmit) {
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

    public function profile(Request $request): Response|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'bio' => ['nullable', 'string', 'max:2000'],
                'instagram_url' => ['nullable', 'url', 'max:255'],
                'twitter_url' => ['nullable', 'url', 'max:255'],
                'website_url' => ['nullable', 'url', 'max:255'],
            ], [
                'name.required' => 'Le nom complet est obligatoire.',
                'instagram_url.url' => "Le lien Instagram doit etre une URL valide.",
                'twitter_url.url' => "Le lien Twitter doit etre une URL valide.",
                'website_url.url' => "Le site web doit etre une URL valide.",
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
