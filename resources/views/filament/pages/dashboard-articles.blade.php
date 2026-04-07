<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $articles = $this->getArticles();
    @endphp

    @include('filament.pages.partials.editorial-page-styles')

    <div class="vp-wrap" wire:poll.5s>
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Editorial</div>
                    <div class="vp-hero-box-title">Articles</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Retrouve les brouillons, les articles en revue et les contenus déjà publiés dans une vue plus claire que la table brute.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu’il se passe ici</h4>
                <p>Tu peux retrouver rapidement un article, filtrer par statut, ouvrir son aperçu, le modifier ou le dépublier si nécessaire.</p>
            </div>
        </div>

        <div class="vp-stats">
            @foreach ([
                ['label' => 'Brouillons', 'value' => $stats['drafts'], 'bg' => '#FFF0B6', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Publiés', 'value' => $stats['published'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'En revue', 'value' => $stats['review'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => "Aujourd'hui", 'value' => $stats['today'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.6)'],
            ] as $card)
                <div class="vp-stat" style="background:{{ $card['bg'] }}">
                    <div class="vp-stat-val" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="vp-stat-label" style="color:{{ $card['sub'] }}">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="vp-filters">
            <input type="text" wire:model.live.debounce.300ms="search" class="vp-input" placeholder="Rechercher un article..." />
            <select wire:model.live="status" class="vp-select">
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="review">En revue</option>
                <option value="published">Publié</option>
                <option value="archived">Dépublié</option>
            </select>
        </div>

        @if ($articles === [])
            <div class="vp-empty">Aucun article ne correspond à la recherche actuelle.</div>
        @else
            <div class="vp-grid">
                @foreach ($articles as $article)
                    @php
                        $status = match($article['status']) {
                            'published' => ['label' => 'Publié', 'bg' => '#ecfdf5', 'text' => '#065f46'],
                            'review' => ['label' => 'En revue', 'bg' => '#fffbeb', 'text' => '#92400e'],
                            'archived' => ['label' => 'Dépublié', 'bg' => '#f3f4f6', 'text' => '#4b5563'],
                            default => ['label' => 'Brouillon', 'bg' => '#EBF1EF', 'text' => '#004241'],
                        };
                    @endphp
                    <section class="vp-card">
                        @if (!empty($article['cover']))
                            <img src="{{ $article['cover'] }}" alt="" class="vp-cover">
                        @endif
                        <div class="vp-card-body">
                            <div class="vp-badges">
                                <span class="vp-badge" style="background:{{ $status['bg'] }};color:{{ $status['text'] }}">{{ $status['label'] }}</span>
                                <span class="vp-badge" style="background:#EBF1EF;color:#004241;font-weight:600">{{ $article['category'] }}</span>
                                <span class="vp-badge" style="background:#FFF0B6;color:#6b5200">{{ $article['type'] }}</span>
                            </div>
                            <h3 class="vp-title">{{ $article['title'] }}</h3>
                            <div class="vp-meta">
                                <span>{{ $article['reading_time'] }} min</span>
                                <span>•</span>
                                <span>{{ $article['published_at'] }}</span>
                                <span>•</span>
                                <span>{{ $article['created_at'] }}</span>
                            </div>
                            <p class="vp-text">{{ $article['excerpt'] }}</p>
                            <div class="vp-actions">
                                <a href="{{ $article['preview_url'] }}" target="_blank" class="vp-btn vp-btn-primary">Aperçu</a>
                                <a href="{{ $article['edit_url'] }}" class="vp-btn vp-btn-secondary">Modifier</a>
                                @if ($article['status'] === 'published')
                                    <button
                                        type="button"
                                        wire:click="unpublish('{{ $article['id'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="unpublish('{{ $article['id'] }}')"
                                        class="vp-btn vp-btn-warning"
                                    >
                                        Dépublier
                                    </button>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
