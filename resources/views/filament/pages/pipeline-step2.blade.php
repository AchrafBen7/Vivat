<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $recentEnriched = $this->getRecentEnriched();
    @endphp

    <style>
        .vp-wrap { display:flex; flex-direction:column; gap:20px; }
        .vp-hero { position:relative; overflow:hidden; border-radius:24px; padding:24px; color:#fff; background:linear-gradient(135deg,#004241 0%,#185B58 58%,#4C807C 100%); }
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
        .vp-stat-help { margin-top:6px; font-size:11px; line-height:1.45; }

        .vp-card { overflow:hidden; border-radius:16px; background:#fff; border:1px solid #D6E1DD; }
        .vp-card-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 24px; }
        .vp-card-head-left { display:flex; align-items:center; gap:10px; min-width:0; }
        .vp-icon-box { width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:10px; flex-shrink:0; }
        .vp-card-title { font-size:14px; font-weight:600; color:#004241; }
        .vp-pill { display:inline-flex; align-items:center; gap:4px; border-radius:999px; padding:6px 12px; font-size:12px; font-weight:600; background:#EBF1EF; color:#004241; text-decoration:none; transition:background 0.15s; }
        .vp-pill:hover { background:#DEE7E4; }
        .vp-pill svg { width:12px; height:12px; }

        .vp-row { display:flex; align-items:center; gap:14px; padding:14px 24px; transition:background 0.15s; }
        .vp-row:hover { background:#F7FAF9; }
        .vp-row + .vp-row { border-top:1px solid #EBF1EF; }
        .vp-avatar { flex-shrink:0; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:12px; font-size:11px; font-weight:700; }
        .vp-row-content { min-width:0; flex:1; }
        .vp-row-title { font-size:14px; font-weight:600; color:#004241; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .vp-row-sub { margin-top:2px; font-size:12px; color:rgba(0,66,65,0.45); }
        .vp-row-time { flex-shrink:0; font-size:12px; color:rgba(0,66,65,0.4); }

        .vp-proposals { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; padding:20px; }
        @media(max-width:768px) { .vp-proposals { grid-template-columns:1fr; } }
        .vp-proposal { position:relative; overflow:hidden; border-radius:16px; border:1px solid #EBF1EF; background:#fff; }
        .vp-proposal-body { padding:16px; }
        .vp-badges { display:flex; align-items:center; flex-wrap:wrap; gap:6px; }
        .vp-badge { display:inline-flex; align-items:center; border-radius:8px; padding:2px 8px; font-size:12px; }
        .vp-proposal-title { margin-top:8px; font-size:14px; font-weight:700; color:#004241; }
        .vp-proposal-text { margin-top:4px; font-size:12px; color:rgba(0,66,65,0.5); line-height:1.5; }
        .vp-btn { margin-top:12px; display:inline-flex; align-items:center; gap:6px; border:none; border-radius:12px; padding:8px 14px; font-size:12px; font-weight:600; color:#fff; background:#004241; cursor:pointer; transition:background 0.15s; }
        .vp-btn:hover { background:#003130; }
        .vp-btn[disabled] { opacity:0.7; cursor:wait; }

        .vp-empty { padding:40px; text-align:center; font-size:14px; color:rgba(0,66,65,0.45); }
        .vp-tip { display:grid; grid-template-columns:42px 1fr; gap:14px; align-items:flex-start; border-radius:16px; padding:18px 20px; background:#F7FAF9; border:1px solid #D6E1DD; }
        .vp-tip-icon { width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:14px; background:#FFF0B6; color:#004241; flex-shrink:0; }
        .vp-tip h4 { font-size:14px; font-weight:700; color:#004241; }
        .vp-tip p { margin-top:4px; font-size:13px; line-height:1.55; color:rgba(0,66,65,0.68); }

        @media(max-width:640px) {
            .vp-card-head { padding:16px; }
            .vp-row { padding:14px 16px; }
        }
    </style>

    <div class="vp-wrap">

        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Étape 2</div>
                    <div class="vp-hero-box-title">Analyse des sujets</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>L'IA enrichit les articles et propose les meilleurs sujets à générer.</p>
                </div>
                <div class="vp-steps" style="display:none" id="vp-steps-step2">
                    <div class="vp-step vp-step-dim">1</div>
                    <div class="vp-step-line"></div>
                    <div class="vp-step vp-step-active">2</div>
                    <div class="vp-step-line"></div>
                    <div class="vp-step vp-step-dim">3</div>
                </div>
            </div>
        </div>
        <script>
            (function(){var s=document.getElementById('vp-steps-step2');if(s&&window.innerWidth>=640)s.style.display='flex';
            window.addEventListener('resize',function(){if(s)s.style.display=window.innerWidth>=640?'flex':'none'})})();
        </script>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu’il se passe ici</h4>
                <p>L’IA lit les contenus repérés, en extrait les idées importantes, évalue leur qualité et propose les sujets qui semblent les plus pertinents pour devenir un article Vivat.</p>
            </div>
        </div>

        <div class="vp-stats">
            @foreach ([
                ['label' => 'Items enrichis', 'value' => $stats['enriched'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)', 'help' => null],
                ['label' => 'En attente', 'value' => $stats['pending'], 'bg' => '#FFF0B6', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)', 'help' => null],
                ['label' => 'Qualité moyenne', 'value' => $stats['avg_quality'].'/100', 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)', 'help' => null],
                ['label' => 'Sujets regroupés', 'value' => $stats['clusters'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.6)', 'help' => 'Les contenus sont rapprochés selon leur sujet, leurs mots-clés et leurs titres proches.'],
            ] as $card)
                <div class="vp-stat" style="background:{{ $card['bg'] }}">
                    <div class="vp-stat-val" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="vp-stat-label" style="color:{{ $card['sub'] }}">{{ $card['label'] }}</div>
                    @if (!empty($card['help']))
                        <div class="vp-stat-help" style="color:{{ $card['sub'] }}">{{ $card['help'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="vp-card">
            <div class="vp-card-head">
                <div class="vp-card-head-left">
                    <div class="vp-icon-box" style="background:#EBF1EF">
                        <svg style="width:16px;height:16px;color:#004241" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25z"/></svg>
                    </div>
                    <h3 class="vp-card-title">Derniers enrichissements</h3>
                </div>
                <a href="{{ \App\Filament\Pages\PipelineEnrichedItems::getUrl() }}" class="vp-pill">
                    Voir tout
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>

            <div>
                @foreach ($recentEnriched as $item)
                    @php
                        $q = $item['quality'];
                        $qBg = $q >= 70 ? '#ecfdf5' : ($q >= 50 ? '#fffbeb' : '#fef2f2');
                        $qText = $q >= 70 ? '#065f46' : ($q >= 50 ? '#92400e' : '#991b1b');
                    @endphp
                    <div class="vp-row">
                        <div class="vp-avatar" style="background:{{ $qBg }};color:{{ $qText }}">{{ $q }}</div>
                        <div class="vp-row-content">
                            <div class="vp-row-title">{{ $item['title'] }}</div>
                            <div class="vp-row-sub">{{ $item['lead'] }}</div>
                        </div>
                        <div class="vp-row-time">{{ $item['enriched_at'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="vp-card">
            <div class="vp-card-head">
                <div class="vp-card-head-left">
                    <div class="vp-icon-box" style="background:#FFF0B6">
                        <svg style="width:16px;height:16px;color:#004241" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
                    </div>
                    <h3 class="vp-card-title">Meilleures idées d'articles</h3>
                </div>
                <a href="{{ \App\Filament\Pages\PipelineProposals::getUrl() }}" class="vp-pill">
                    Voir tout
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>

            @if ($proposals === [])
                <div class="vp-empty">Aucune proposition. Lance d'abord l'enrichissement.</div>
            @else
                <div class="vp-proposals">
                    @foreach ($proposals as $index => $proposal)
                        @php
                            $score = $proposal['score'] ?? 0;
                            $scoreBg = $score >= 70 ? '#ecfdf5' : ($score >= 45 ? '#fffbeb' : '#fef2f2');
                            $scoreText = $score >= 70 ? '#065f46' : ($score >= 45 ? '#92400e' : '#991b1b');
                            $scoreBar = $score >= 70 ? '#10b981' : ($score >= 45 ? '#f59e0b' : '#ef4444');
                            $categoryName = $proposal['category']['name'] ?? null;
                        @endphp
                        <div class="vp-proposal">
                            <div style="height:4px;background:#EBF1EF">
                                <div style="height:100%;width:{{ $score }}%;background:{{ $scoreBar }};border-radius:0 9999px 9999px 0"></div>
                            </div>
                            <div class="vp-proposal-body">
                                <div class="vp-badges">
                                    <span class="vp-badge" style="background:{{ $scoreBg }};color:{{ $scoreText }};font-weight:700">{{ $score }}/100</span>
                                    @if ($categoryName)
                                        <span class="vp-badge" style="background:#EBF1EF;color:#004241;font-weight:600">{{ $categoryName }}</span>
                                    @endif
                                    <span class="vp-badge" style="background:#f3f4f6;color:#6b7280">{{ count($proposal['items'] ?? []) }} source(s)</span>
                                </div>
                                <h4 class="vp-proposal-title">{{ $proposal['topic'] ?? 'Sans titre' }}</h4>
                                <p class="vp-proposal-text">{{ \Illuminate\Support\Str::limit($proposal['reasoning'] ?? '', 100) }}</p>
                                <button
                                    type="button"
                                    wire:click="generateProposal({{ $index }})"
                                    wire:loading.attr="disabled"
                                    class="vp-btn"
                                >
                                    <svg wire:loading.remove wire:target="generateProposal({{ $index }})" style="width:14px;height:14px" fill="currentColor" viewBox="0 0 20 20"><path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"/></svg>
                                    <svg wire:loading wire:target="generateProposal({{ $index }})" style="width:14px;height:14px" class="animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                                    Générer
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
