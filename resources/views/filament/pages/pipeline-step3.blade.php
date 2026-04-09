<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $drafts = $this->getDraftArticles();
        $recentPublished = $this->getRecentPublished();
        $recentActivity = $this->getRecentActivity();
    @endphp

    <style>
        .vp-wrap { display:flex; flex-direction:column; gap:20px; }
        .vp-hero { position:relative; overflow:hidden; border-radius:24px; padding:24px; color:#fff; background:#004241; }
        .vp-hero-inner { position:relative; display:flex; align-items:center; gap:16px; }
        .vp-hero-box { flex-shrink:0; min-width:180px; padding:12px 16px; border-radius:16px; background:rgba(255,255,255,0.12); backdrop-filter:blur(8px); }
        .vp-hero-box-step { font-size:11px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:rgba(255,255,255,0.72); }
        .vp-hero-box-title { margin-top:4px; font-size:18px; font-weight:700; line-height:1.1; }
        .vp-hero-text h2 { font-size:20px; font-weight:700; letter-spacing:-0.02em; }
        .vp-hero-text p { margin-top:4px; font-size:14px; color:rgba(255,255,255,0.65); }
        .vp-hero-circle { position:absolute; border-radius:50%; background:rgba(255,255,255,0.05); pointer-events:none; }
        .vp-steps { display:flex; align-items:center; gap:8px; flex-shrink:0; }
        .vp-step { width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:12px; font-weight:600; }
        .vp-step-active { background:#fff; color:#004241; }
        .vp-step-dim { background:rgba(255,255,255,0.15); color:rgba(255,255,255,0.5); }
        .vp-step-line { width:24px; height:1px; background:rgba(255,255,255,0.3); }

        .vp-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        @media(max-width:640px) { .vp-stats { grid-template-columns:repeat(2,1fr); } }
        .vp-stat { border-radius:16px; padding:20px; transition:transform 0.2s; }
        .vp-stat:hover { transform:translateY(-2px); }
        .vp-stat-val { font-size:30px; font-weight:700; }
        .vp-stat-label { margin-top:4px; font-size:12px; font-weight:500; }

        .vp-card { overflow:hidden; border-radius:16px; background:#fff; border:1px solid #D6E1DD; }
        .vp-card-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 24px; }
        .vp-card-head-left { display:flex; align-items:center; gap:10px; min-width:0; }
        .vp-icon-box { width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:10px; flex-shrink:0; }
        .vp-card-title { font-size:14px; font-weight:600; color:#004241; }
        .vp-pill { display:inline-flex; align-items:center; gap:4px; border-radius:999px; padding:6px 12px; font-size:12px; font-weight:600; background:#EBF1EF; color:#004241; text-decoration:none; transition:background 0.15s; }
        .vp-pill:hover { background:#DEE7E4; }
        .vp-pill svg { width:12px; height:12px; }

        .vp-empty { padding:40px; text-align:center; }
        .vp-empty-text { margin-top:8px; font-size:14px; color:rgba(0,66,65,0.5); }

        .vp-draft-row { display:flex; align-items:center; gap:16px; padding:14px 24px; text-decoration:none; transition:background 0.15s; }
        .vp-draft-row:hover { background:#F7FAF9; }
        .vp-draft-row + .vp-draft-row { border-top:1px solid #EBF1EF; }
        .vp-draft-main { min-width:0; flex:1; display:flex; align-items:center; gap:16px; text-decoration:none; }
        .vp-thumb { width:80px; height:56px; flex-shrink:0; object-fit:cover; border-radius:12px; }
        .vp-thumb-fallback { width:80px; height:56px; flex-shrink:0; display:flex; align-items:center; justify-content:center; border-radius:12px; background:#EBF1EF; }
        .vp-row-content { min-width:0; flex:1; }
        .vp-row-title { font-size:14px; font-weight:600; color:#004241; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .vp-meta { margin-top:4px; display:flex; align-items:center; flex-wrap:wrap; gap:8px; font-size:12px; color:rgba(0,66,65,0.45); }
        .vp-badge { display:inline-flex; align-items:center; border-radius:8px; padding:2px 8px; background:#EBF1EF; color:#004241; font-size:12px; font-weight:500; }
        .vp-actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }
        .vp-btn-link { display:inline-flex; align-items:center; justify-content:center; border-radius:10px; padding:8px 12px; text-decoration:none; font-size:12px; font-weight:600; color:#004241; background:#EBF1EF; transition:background 0.15s; }
        .vp-btn-link:hover { background:#DEE7E4; }
        .vp-btn-publish { display:inline-flex; align-items:center; justify-content:center; border:none; border-radius:10px; padding:8px 12px; font-size:12px; font-weight:600; color:#fff; background:#004241; cursor:pointer; transition:background 0.15s; }
        .vp-btn-publish:hover { background:#003130; }
        .vp-btn-publish[disabled] { opacity:0.7; cursor:wait; }
        .vp-btn-secondary { display:inline-flex; align-items:center; justify-content:center; border:none; border-radius:10px; padding:10px 14px; font-size:13px; font-weight:600; color:#004241; background:#EBF1EF; cursor:pointer; transition:background 0.15s; }
        .vp-btn-secondary:hover { background:#DEE7E4; }
        .vp-modal-backdrop { position:fixed; inset:0; background:rgba(4,20,20,0.48); z-index:70; display:flex; align-items:center; justify-content:center; padding:24px; }
        .vp-modal { width:min(560px, 100%); border-radius:22px; background:#fff; border:1px solid #D6E1DD; box-shadow:0 24px 70px rgba(0,0,0,0.14); overflow:hidden; }
        .vp-modal-head { padding:22px 24px 14px; }
        .vp-modal-title { font-size:20px; font-weight:700; color:#004241; }
        .vp-modal-sub { margin-top:6px; font-size:14px; line-height:1.6; color:rgba(0,66,65,0.65); }
        .vp-modal-body { padding:0 24px 24px; display:flex; flex-direction:column; gap:16px; }
        .vp-field-label { display:block; font-size:13px; font-weight:700; color:#004241; margin-bottom:8px; }
        .vp-select { width:100%; border-radius:12px; border:1px solid #D6E1DD; padding:12px 14px; font-size:14px; color:#004241; background:#fff; }
        .vp-note { border-radius:14px; background:#F7FAF9; border:1px solid #D6E1DD; padding:14px 16px; font-size:13px; line-height:1.6; color:rgba(0,66,65,0.72); }
        .vp-modal-actions { padding:18px 24px 24px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #EBF1EF; }

        .vp-cols { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        @media(max-width:1024px) { .vp-cols { grid-template-columns:1fr; } }
        .vp-row { display:flex; align-items:center; gap:14px; padding:12px 24px; text-decoration:none; transition:background 0.15s; }
        .vp-row:hover { background:#F7FAF9; }
        .vp-row + .vp-row { border-top:1px solid #EBF1EF; }
        .vp-small-thumb { width:56px; height:40px; flex-shrink:0; object-fit:cover; border-radius:8px; }
        .vp-row-sub { margin-top:2px; font-size:12px; color:rgba(0,66,65,0.4); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .vp-arrow { flex-shrink:0; color:rgba(0,66,65,0.3); }
        .vp-dot { display:inline-block; width:8px; height:8px; border-radius:999px; flex-shrink:0; }
        .vp-time { font-size:12px; color:rgba(0,66,65,0.4); flex-shrink:0; }
        .vp-tip { display:grid; grid-template-columns:42px 1fr; gap:14px; align-items:flex-start; border-radius:16px; padding:18px 20px; background:#F7FAF9; border:1px solid #D6E1DD; }
        .vp-tip-icon { width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:14px; background:#FFF0B6; color:#004241; flex-shrink:0; }
        .vp-tip h4 { font-size:14px; font-weight:700; color:#004241; }
        .vp-tip p { margin-top:4px; font-size:13px; line-height:1.55; color:rgba(0,66,65,0.68); }
        @media(max-width:640px) {
            .vp-card-head { padding:16px; }
            .vp-draft-row, .vp-row { padding:14px 16px; }
        }
    </style>

    <div class="vp-wrap">

        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Étape 3</div>
                    <div class="vp-hero-box-title">Brouillons IA</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Les brouillons IA sont prêts à relire et publier.</p>
                </div>
                <div class="vp-steps" style="display:none" id="vp-steps-step3">
                    <div class="vp-step vp-step-dim">1</div>
                    <div class="vp-step-line"></div>
                    <div class="vp-step vp-step-dim">2</div>
                    <div class="vp-step-line"></div>
                    <div class="vp-step vp-step-active">3</div>
                </div>
            </div>
        </div>
        <script>
            (function(){var s=document.getElementById('vp-steps-step3');if(s&&window.innerWidth>=640)s.style.display='flex';
            window.addEventListener('resize',function(){if(s)s.style.display=window.innerWidth>=640?'flex':'none'})})();
        </script>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Les meilleurs sujets deviennent ici des brouillons prêts à être relus. Il suffit ensuite de les vérifier, de les modifier si nécessaire, puis de les publier.</p>
            </div>
        </div>

        <div class="vp-stats">
            @foreach ([
                ['label' => 'Brouillons', 'value' => $stats['drafts'], 'bg' => '#FFF0B6', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => "Aujourd'hui", 'value' => $stats['today'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.6)'],
            ] as $card)
                <div class="vp-stat" style="background:{{ $card['bg'] }}">
                    <div class="vp-stat-val" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="vp-stat-label" style="color:{{ $card['sub'] }}">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="vp-card">
            <div class="vp-card-head">
                <div class="vp-card-head-left">
                    <div class="vp-icon-box" style="background:#FFF0B6">
                        <svg style="width:16px;height:16px;color:#004241" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    </div>
                    <h3 class="vp-card-title">Brouillons à relire</h3>
                    @if ($stats['drafts'] > 0)
                        <span class="vp-badge" style="background:#FFF0B6;font-weight:700">{{ $stats['drafts'] }}</span>
                    @endif
                </div>
                <a href="{{ \App\Filament\Pages\PipelineArticles::getUrl() }}" class="vp-pill">
                    Voir tout
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>

            @if (empty($drafts))
                <div class="vp-empty">
                    <svg style="width:40px;height:40px;color:#10b981;margin:0 auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="vp-empty-text">Aucun brouillon en attente. Tout est publié !</p>
                </div>
            @else
                <div>
                    @foreach ($drafts as $draft)
                        <div class="vp-draft-row">
                            <a href="{{ $draft['preview_url'] }}" target="_blank" class="vp-draft-main">
                                @if (!empty($draft['cover']))
                                    <img src="{{ $draft['cover'] }}" alt="" class="vp-thumb">
                                @else
                                    <div class="vp-thumb-fallback">
                                        <svg style="width:20px;height:20px;color:rgba(0,66,65,0.3)" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                                    </div>
                                @endif
                                <div class="vp-row-content">
                                    <div class="vp-row-title">{{ $draft['title'] }}</div>
                                    <div class="vp-meta">
                                        <span class="vp-badge">{{ $draft['category'] }}</span>
                                        <span>{{ $draft['word_count'] }} mots</span>
                                        <span>{{ $draft['created_at'] }}</span>
                                    </div>
                                </div>
                            </a>
                            <div class="vp-actions" onclick="event.stopPropagation()">
                                <a href="{{ $draft['edit_url'] }}" class="vp-btn-link">Modifier</a>
                                <button
                                    type="button"
                                    wire:click.prevent="openPublishModal('{{ $draft['id'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="openPublishModal('{{ $draft['id'] }}')"
                                    class="vp-btn-publish"
                                    @disabled(! $draft['is_publishable'])
                                >
                                    Publier
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="vp-cols">
            <div class="vp-card">
                <div class="vp-card-head">
                    <div class="vp-card-head-left">
                        <div class="vp-icon-box" style="background:#EBF1EF">
                            <svg style="width:16px;height:16px;color:#004241" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="vp-card-title">Derniers publiés</h3>
                    </div>
                </div>
                <div>
                    @foreach ($recentPublished as $article)
                        <a href="{{ $article['url'] }}" target="_blank" class="vp-row">
                            @if (!empty($article['cover']))
                                <img src="{{ $article['cover'] }}" alt="" class="vp-small-thumb">
                            @endif
                            <div class="vp-row-content">
                                <div class="vp-row-title">{{ $article['title'] }}</div>
                                <div class="vp-row-sub">{{ $article['category'] }} &middot; {{ $article['published_at'] }}</div>
                            </div>
                            <svg class="vp-arrow" style="width:14px;height:14px" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="vp-card">
                <div class="vp-card-head">
                    <div class="vp-card-head-left">
                        <div class="vp-icon-box" style="background:#EBF1EF">
                            <svg style="width:16px;height:16px;color:#004241" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="vp-card-title">Activité récente</h3>
                    </div>
                    <a href="{{ \App\Filament\Pages\PipelineCronJobs::getUrl() }}" class="vp-pill">
                        Voir tout
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
                <div>
                    @foreach ($recentActivity as $job)
                        @php
                            $dot = match($job['status']) {
                                'completed' => '#10b981',
                                'failed' => '#ef4444',
                                'running' => '#3b82f6',
                                default => '#9ca3af',
                            };
                        @endphp
                        <div class="vp-row">
                            <span class="vp-dot" style="background:{{ $dot }}"></span>
                            <div class="vp-row-content">
                                <div class="vp-row-title">{{ $job['type'] }}</div>
                            </div>
                            <div class="vp-time">{{ $job['time'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    @if ($this->publishModalOpen)
        <div class="vp-modal-backdrop" wire:keydown.escape="closePublishModal" tabindex="0">
            <div class="vp-modal">
                <div class="vp-modal-head">
                    <div class="vp-modal-title">Confirmer la publication</div>
                    <div class="vp-modal-sub">Le brouillon ne sera pas publié immédiatement après le clic. Vérifie d'abord la catégorie, puis confirme explicitement la mise en ligne.</div>
                </div>

                <div class="vp-modal-body">
                    <div>
                        <label class="vp-field-label" for="publish-category">Catégorie</label>
                        <select id="publish-category" class="vp-select" wire:model="publishCategoryId">
                            <option value="">Choisir une catégorie</option>
                            @foreach ($this->getCategoryOptions() as $categoryId => $categoryName)
                                <option value="{{ $categoryId }}">{{ $categoryName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="vp-note">
                        Si le brouillon n'a pas encore de catégorie, tu peux l'ajouter ici avant publication. Tu peux aussi en profiter pour corriger la catégorie proposée par l'IA.
                    </div>
                </div>

                <div class="vp-modal-actions">
                    <button type="button" class="vp-btn-secondary" wire:click="closePublishModal">Annuler</button>
                    <button
                        type="button"
                        class="vp-btn-publish"
                        wire:click="confirmPublishDraft"
                        wire:loading.attr="disabled"
                        wire:target="confirmPublishDraft"
                    >
                        Confirmer la publication
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
