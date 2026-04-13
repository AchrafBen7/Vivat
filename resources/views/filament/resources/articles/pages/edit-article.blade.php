<x-filament-panels::page>
    @php
        $article = $this->getArticleData();

        $status = match($article['status']) {
            'published' => ['label' => 'Publié', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            'review' => ['label' => 'En revue', 'bg' => '#fffbeb', 'text' => '#92400e'],
            'archived' => ['label' => 'Dépublié', 'bg' => '#f3f4f6', 'text' => '#4b5563'],
            default => ['label' => 'Brouillon', 'bg' => '#EBF1EF', 'text' => '#004241'],
        };
    @endphp

    @include('filament.pages.partials.editorial-page-styles')

    <div class="vp-wrap">
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Editorial</div>
                    <div class="vp-hero-box-title">Modifier article</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Corrige ici le titre, la catégorie, la cover et le contenu sans passer par la vue Filament brute.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Tu vois d’abord le résumé éditorial de l’article, puis juste en dessous le formulaire complet pour le modifier et l’enregistrer.</p>
            </div>
        </div>

        <section class="vp-card">
            <div class="vp-card-body" style="display:grid;grid-template-columns:120px minmax(0,1fr);gap:20px;align-items:start">
                <div style="width:120px;height:120px;border-radius:24px;overflow:hidden;background:#f3f4f6;display:flex;align-items:center;justify-content:center">
                    @if (!empty($article['cover']))
                        <img src="{{ $article['cover'] }}" alt="" style="width:100%;height:100%;object-fit:cover">
                    @else
                        <span style="font-size:12px;color:rgba(0,66,65,.45)">Sans image</span>
                    @endif
                </div>

                <div>
                    <div class="vp-badges">
                        <span class="vp-badge" style="background:{{ $status['bg'] }};color:{{ $status['text'] }}">{{ $status['label'] }}</span>
                        <span class="vp-badge" style="background:#EBF1EF;color:#004241">{{ $article['category'] }}</span>
                        <span class="vp-badge" style="background:#FFF0B6;color:#6b5200">{{ $article['type'] }}</span>
                    </div>

                    <h2 class="vp-title" style="font-size:28px">{{ $article['title'] }}</h2>

                    <div class="vp-meta">
                        <span>{{ $article['reading_time'] }} min</span>
                        <span>•</span>
                        <span>{{ $article['published_at'] }}</span>
                        <span>•</span>
                        <span>Mis à jour le {{ $article['updated_at'] }}</span>
                    </div>

                    @if ($article['excerpt'] !== '')
                        <p class="vp-text" style="margin-top:12px">{{ $article['excerpt'] }}</p>
                    @endif
                </div>
            </div>
        </section>

        <section class="vp-card">
            <div class="vp-card-body">
                <h3 class="vp-title" style="margin-top:0">Formulaire d’édition</h3>
                <p class="vp-text" style="margin-top:6px">Modifie les champs utiles, puis enregistre. Les actions rapides restent disponibles en haut de page.</p>

                <form wire:submit="save" style="margin-top:20px;display:grid;gap:18px">
                    {{ $this->form }}

                    <div class="vp-actions" style="justify-content:flex-end">
                        <a href="{{ \App\Filament\Pages\DashboardArticles::getUrl() }}" class="vp-btn vp-btn-secondary">Retour aux articles</a>
                        <button type="submit" class="vp-btn vp-btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-filament-panels::page>
