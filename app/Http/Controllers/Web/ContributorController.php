<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContributorController extends Controller
{
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
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        $submissions = Submission::where('user_id', $user->id)
            ->with('category')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'slug' => $s->slug,
                'status' => $s->status,
                'created_at' => $s->created_at?->format('d/m/Y'),
                'category' => $s->category ? ['name' => $s->category->name] : null,
            ])
            ->all();

        return $this->renderContributorPage('articles', 'site.contributor.articles', [
            'user' => $user,
            'submissions' => $submissions,
        ]);
    }

    public function newArticle(Request $request): Response|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'title'       => ['required', 'string', 'max:255'],
                'excerpt'     => ['nullable', 'string', 'max:500'],
                'content'     => ['required', 'string', 'min:100'],
                'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
                'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
                'status'      => ['sometimes', 'in:draft,submitted'],
            ], [
                'title.required' => 'Le titre est obligatoire.',
                'content.required' => 'Le contenu est obligatoire.',
                'content.min' => 'Le contenu doit contenir au moins 100 caractères.',
            ]);

            $status = $request->input('status') === 'submitted' ? 'pending' : 'draft';

            Submission::create([
                'user_id'     => $request->user()->id,
                'title'       => $validated['title'],
                'excerpt'     => $validated['excerpt'] ?? null,
                'content'     => $validated['content'],
                'category_id' => $validated['category_id'] ?? null,
                'status'      => $status,
            ]);

            return redirect()->route('contributor.dashboard')->with('success', $status === 'pending' ? 'Article soumis avec succès !' : 'Brouillon enregistré.');
        }

        $categories = Category::orderBy('name')->get(['id', 'name'])->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all();
        $errors = $request->session()->get('errors');
        $old = $request->old();

        return $this->renderContributorPage('new', 'site.contributor.new', [
            'categories' => $categories,
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
        ]);
    }

    public function profile(Request $request): Response
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'bio' => ['nullable', 'string', 'max:2000'],
            ], [
                'name.required' => 'Le nom complet est obligatoire.',
            ]);

            $request->user()->update([
                'name' => $validated['name'],
                'bio' => $validated['bio'] ?? null,
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
