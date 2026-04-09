<x-filament-panels::page>
    <style>
        .vp-wrap { display:flex; flex-direction:column; gap:20px; }
        .vp-hero { position:relative; overflow:hidden; border-radius:24px; padding:24px; color:#fff; background:linear-gradient(135deg,#004241 0%,#185B58 58%,#4C807C 100%); }
        .vp-hero-inner { position:relative; display:flex; align-items:center; gap:16px; }
        .vp-hero-box { flex-shrink:0; min-width:0; padding:0; border-radius:0; background:transparent; backdrop-filter:none; }
        .vp-hero-box-step { font-size:11px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:rgba(255,255,255,0.72); }
        .vp-hero-box-title { margin-top:4px; font-size:18px; font-weight:700; line-height:1.1; }
        .vp-hero-text p { margin-top:4px; font-size:14px; color:rgba(255,255,255,0.68); }
        .vp-hero-circle { position:absolute; border-radius:50%; background:rgba(255,255,255,0.05); pointer-events:none; }

        .vp-tip { display:grid; grid-template-columns:42px 1fr; gap:14px; align-items:flex-start; border-radius:16px; padding:18px 20px; background:#F7FAF9; border:1px solid #D6E1DD; }
        .vp-tip-icon { width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:14px; background:#FFF0B6; color:#004241; flex-shrink:0; }
        .vp-tip h4 { font-size:14px; font-weight:700; color:#004241; }
        .vp-tip p { margin-top:4px; font-size:13px; line-height:1.55; color:rgba(0,66,65,0.68); }

        .vp-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px; }
        @media(max-width:1024px) { .vp-grid { grid-template-columns:1fr; } }

        .vp-card { overflow:hidden; border-radius:18px; background:#fff; border:1px solid #D6E1DD; }
        .vp-card-top { height:4px; background:#EBF1EF; }
        .vp-card-top-fill { height:100%; border-radius:0 999px 999px 0; }
        .vp-card-body { padding:20px; }
        .vp-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; }
        .vp-badges { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .vp-badge { display:inline-flex; align-items:center; gap:6px; border-radius:10px; padding:4px 10px; font-size:12px; font-weight:700; }
        .vp-badge-soft { background:#EBF1EF; color:#004241; font-weight:600; }
        .vp-title { margin-top:10px; font-size:18px; font-weight:700; line-height:1.25; color:#004241; }
        .vp-text { margin-top:6px; font-size:13px; line-height:1.6; color:rgba(0,66,65,0.62); }
        .vp-generate { display:inline-flex; align-items:center; gap:8px; border:none; border-radius:12px; padding:10px 14px; font-size:12px; font-weight:700; color:#fff; background:#004241; cursor:pointer; transition:background .15s; white-space:nowrap; }
        .vp-generate:hover { background:#003130; }
        .vp-generate[disabled] { opacity:.7; cursor:wait; }

        .vp-stats { margin-top:16px; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
        .vp-stat { border-radius:14px; padding:14px; background:#F7FAF9; text-align:center; }
        .vp-stat-value { font-size:22px; font-weight:700; color:#004241; }
        .vp-stat-label { margin-top:4px; font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:rgba(0,66,65,0.45); }

        .vp-section { margin-top:16px; }
        .vp-section-label { margin-bottom:8px; font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:rgba(0,66,65,0.4); }
        .vp-keywords { display:flex; flex-wrap:wrap; gap:6px; }
        .vp-keyword { display:inline-flex; border-radius:8px; padding:4px 8px; font-size:11px; font-weight:600; background:#FFF0B6; color:#6b5200; }
        .vp-grouped-note { margin-top:10px; border-radius:12px; padding:10px 12px; background:#F7FAF9; border:1px solid #EBF1EF; }
        .vp-grouped-note-title { font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:rgba(0,66,65,0.4); }
        .vp-grouped-note-text { margin-top:6px; font-size:12px; line-height:1.55; color:rgba(0,66,65,0.62); }

        .vp-sources { margin-top:10px; display:flex; flex-direction:column; gap:8px; }
        .vp-source { display:flex; align-items:center; gap:12px; border-radius:12px; border:1px solid #EBF1EF; background:#F7FAF9; padding:10px 12px; }
        .vp-source-score { width:34px; height:34px; flex-shrink:0; display:flex; align-items:center; justify-content:center; border-radius:999px; font-size:12px; font-weight:700; }
        .vp-source-main { min-width:0; flex:1; }
        .vp-source-title { font-size:12px; font-weight:600; color:#004241; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .vp-source-sub { margin-top:2px; font-size:11px; color:rgba(0,66,65,0.45); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .vp-empty { border-radius:18px; border:1px dashed #C9D8D4; background:#fff; padding:48px 24px; text-align:center; color:rgba(0,66,65,0.5); }

        @media(max-width:640px) {
            .vp-head { flex-direction:column; }
            .vp-stats { grid-template-columns:1fr; }
        }
    </style>

    <div class="vp-wrap">
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Assistant IA</div>
                    <div class="vp-hero-box-title">Idées d'articles</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Les meilleurs sujets détectés sont classés ici pour lancer rapidement la création d'un brouillon.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Chaque carte représente un sujet jugé intéressant par l'IA. Le classement combine la fraîcheur du sujet, la qualité des contenus trouvés, leur potentiel SEO et la diversité des sources.</p>
            </div>
        </div>

        @if ($proposals === [])
            <div class="vp-empty">
                Aucune idée d'article disponible pour le moment. Lance d'abord le repérage puis l'analyse IA.
            </div>
        @else
            <div class="vp-grid">
                @foreach ($proposals as $index => $proposal)
                    @php
                        $score = (int) ($proposal['score'] ?? 0);
                        $scoreFill = $score >= 70 ? '#10b981' : ($score >= 45 ? '#f59e0b' : '#ef4444');
                        $scoreBg = $score >= 70 ? '#ecfdf5' : ($score >= 45 ? '#fffbeb' : '#fef2f2');
                        $scoreText = $score >= 70 ? '#065f46' : ($score >= 45 ? '#92400e' : '#991b1b');
                        $typeLabel = match($proposal['suggested_article_type'] ?? 'standard') {
                            'hot_news' => 'Hot news',
                            'long_form' => 'Long form',
                            default => 'Standard',
                        };
                        $categoryName = $proposal['category']['name'] ?? null;
                    @endphp

                    <section class="vp-card">
                        <div class="vp-card-top">
                            <div class="vp-card-top-fill" style="width:{{ $score }}%;background:{{ $scoreFill }}"></div>
                        </div>
                        <div class="vp-card-body">
                            <div class="vp-head">
                                <div style="min-width:0;flex:1">
                                    <div class="vp-badges">
                                        <span class="vp-badge" style="background:{{ $scoreBg }};color:{{ $scoreText }}">
                                            <svg style="width:13px;height:13px" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            {{ $score }}/100
                                        </span>
                                        <span class="vp-badge vp-badge-soft">{{ $typeLabel }}</span>
                                        @if ($categoryName)
                                            <span class="vp-badge vp-badge-soft">{{ $categoryName }}</span>
                                        @endif
                                    </div>
                                    <h3 class="vp-title">{{ $proposal['topic'] ?? 'Sujet sans titre' }}</h3>
                                    <p class="vp-text">{{ \Illuminate\Support\Str::limit($proposal['reasoning'] ?? '', 150) }}</p>
                                    @if (!empty($proposal['items']))
                                        <div class="vp-grouped-note">
                                            <div class="vp-grouped-note-title">Sujet formé à partir de ces contenus</div>
                                            <div class="vp-grouped-note-text">
                                                {{ collect($proposal['items'])->pluck('title')->filter()->take(3)->implode(' · ') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <button
                                    type="button"
                                    wire:click="generateProposal({{ $index }})"
                                    wire:loading.attr="disabled"
                                    wire:target="generateProposal({{ $index }})"
                                    class="vp-generate"
                                >
                                    <span wire:loading.remove wire:target="generateProposal({{ $index }})">Générer</span>
                                    <span wire:loading wire:target="generateProposal({{ $index }})">Création...</span>
                                </button>
                            </div>

                            <div class="vp-stats">
                                <div class="vp-stat">
                                    <div class="vp-stat-value">{{ $proposal['source_count'] ?? 0 }}</div>
                                    <div class="vp-stat-label">Sources</div>
                                </div>
                                <div class="vp-stat">
                                    <div class="vp-stat-value">{{ $proposal['avg_quality'] ?? 0 }}/100</div>
                                    <div class="vp-stat-label">Qualité</div>
                                </div>
                                <div class="vp-stat">
                                    <div class="vp-stat-value">{{ count($proposal['items'] ?? []) }}</div>
                                    <div class="vp-stat-label">Articles</div>
                                </div>
                            </div>

                            @if (!empty($proposal['seo_keywords']))
                                <div class="vp-section">
                                    <div class="vp-section-label">Mots-clés SEO</div>
                                    <div class="vp-keywords">
                                        @foreach (array_slice($proposal['seo_keywords'] ?? [], 0, 8) as $keyword)
                                            @php $word = is_array($keyword) ? ($keyword['word'] ?? '') : $keyword; @endphp
                                            @if ($word !== '')
                                                <span class="vp-keyword">{{ $word }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (!empty($proposal['items']))
                                <div class="vp-section">
                                    <div class="vp-section-label">{{ count($proposal['items']) }} contenu(s) regroupé(s)</div>
                                    <div class="vp-sources">
                                        @foreach ($proposal['items'] as $item)
                                            @php
                                                $q = (int) ($item['quality_score'] ?? 0);
                                                $qBg = $q >= 70 ? '#ecfdf5' : ($q >= 50 ? '#fffbeb' : '#fef2f2');
                                                $qText = $q >= 70 ? '#065f46' : ($q >= 50 ? '#92400e' : '#991b1b');
                                            @endphp
                                            <div class="vp-source">
                                                <div class="vp-source-score" style="background:{{ $qBg }};color:{{ $qText }}">{{ $q }}</div>
                                                <div class="vp-source-main">
                                                    <div class="vp-source-title">{{ $item['title'] ?? 'Sans titre' }}</div>
                                                    <div class="vp-source-sub">{{ $item['source'] ?? 'Source inconnue' }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
