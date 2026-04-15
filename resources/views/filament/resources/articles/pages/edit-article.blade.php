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

    <style>
        .vp-edit-layout {
            display: grid;
            gap: 24px;
            align-items: start;
            grid-template-columns: minmax(0, 1fr) 320px;
        }

        .vp-edit-main {
            display: grid;
            gap: 24px;
        }

        .vp-edit-sidebar {
            display: grid;
            gap: 18px;
            position: sticky;
            top: 24px;
        }

        @media (max-width: 1100px) {
            .vp-edit-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .vp-edit-sidebar {
                position: static;
            }
        }

        @media (max-width: 720px) {
            .vp-edit-summary {
                grid-template-columns: 1fr !important;
            }
        }

        .vp-edit-form-card {
            overflow: hidden;
        }

        .vp-edit-form-shell {
            display: grid;
            gap: 18px;
        }

        .vp-edit-sidebar-card {
            padding: 18px 20px;
            border-radius: 20px;
            background: #f7faf9;
            border: 1px solid rgba(0, 66, 65, .08);
        }
    </style>

    <div class="vp-wrap">
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Editorial</div>
                    <div class="vp-hero-box-title">Modifier article</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Cette page regroupe l’essentiel de manière plus claire: un résumé rapide de l’article, le formulaire d’édition au centre, puis les informations utiles dans une colonne latérale.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Le résumé aide à garder le contexte de l’article pendant l’édition. Le formulaire reste complet, mais la page est maintenant plus aérée et plus facile à parcourir.</p>
            </div>
        </div>

        <section class="vp-card">
            <div class="vp-card-body vp-edit-summary" style="display:grid;grid-template-columns:120px minmax(0,1fr);gap:20px;align-items:start">
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

        <div class="vp-edit-layout" style="margin-top:24px">
            <div class="vp-edit-main">
                <section class="vp-card vp-edit-form-card">
                    <div class="vp-card-body">
                        <div style="display:flex;flex-wrap:wrap;align-items:end;justify-content:space-between;gap:16px">
                            <div>
                                <h3 class="vp-title" style="margin-top:0">Formulaire d’édition</h3>
                                <p class="vp-text" style="margin-top:6px">Le formulaire prend plus de place pour éviter les champs écrasés et garder une lecture plus simple.</p>
                            </div>
                        </div>

                        <form wire:submit="save" style="margin-top:20px">
                            <div class="vp-edit-form-shell">
                                {{ $this->form }}

                                <div class="vp-actions" style="justify-content:flex-end">
                                    <a href="{{ \App\Filament\Pages\DashboardArticles::getUrl() }}" class="vp-btn vp-btn-secondary">Retour aux articles</a>
                                    <button type="submit" class="vp-btn vp-btn-primary">Enregistrer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>

            <aside class="vp-edit-sidebar">
                <section class="vp-card">
                    <div class="vp-card-body">
                        <h3 class="vp-title" style="margin-top:0">Résumé rapide</h3>
                        <p class="vp-text" style="margin-top:6px">Les infos principales restent visibles pendant l’édition pour éviter les allers-retours.</p>

                        <div style="display:grid;gap:10px;margin-top:18px">
                            <div class="vp-edit-sidebar-card">
                                <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(0,66,65,.52)">Statut</div>
                                <div style="margin-top:6px;font-size:15px;font-weight:700;color:#004241">{{ $status['label'] }}</div>
                            </div>
                            <div class="vp-edit-sidebar-card">
                                <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(0,66,65,.52)">Catégorie</div>
                                <div style="margin-top:6px;font-size:15px;font-weight:700;color:#004241">{{ $article['category'] }}</div>
                            </div>
                            <div class="vp-edit-sidebar-card">
                                <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(0,66,65,.52)">Lecture</div>
                                <div style="margin-top:6px;font-size:15px;font-weight:700;color:#004241">{{ $article['reading_time'] }} min</div>
                            </div>
                            <div class="vp-edit-sidebar-card">
                                <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(0,66,65,.52)">Dernière mise à jour</div>
                                <div style="margin-top:6px;font-size:15px;font-weight:700;color:#004241">{{ $article['updated_at'] }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="vp-card">
                    <div class="vp-card-body">
                        <h3 class="vp-title" style="margin-top:0">Raccourcis utiles</h3>
                        <p class="vp-text" style="margin-top:6px">Les actions du haut restent disponibles pendant toute la modification.</p>

                        <div style="display:grid;gap:10px;margin-top:18px">
                            <div class="vp-edit-sidebar-card">
                                <div style="font-size:14px;font-weight:700;color:#004241">Aperçu admin</div>
                                <div style="margin-top:4px;font-size:13px;line-height:1.55;color:rgba(0,66,65,.7)">Valide le rendu lecteur avec le bandeau d’aperçu et le cadre de lecture.</div>
                            </div>
                            <div class="vp-edit-sidebar-card">
                                <div style="font-size:14px;font-weight:700;color:#004241">Nouvelle cover IA</div>
                                <div style="margin-top:4px;font-size:13px;line-height:1.55;color:rgba(0,66,65,.7)">Relance une image si le visuel actuel ne correspond pas au sujet ou à la catégorie.</div>
                            </div>
                            <div class="vp-edit-sidebar-card">
                                <div style="font-size:14px;font-weight:700;color:#004241">Enregistrer</div>
                                <div style="margin-top:4px;font-size:13px;line-height:1.55;color:rgba(0,66,65,.7)">Sauvegarde le formulaire après chaque bloc important au lieu d’attendre la fin complète.</div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
