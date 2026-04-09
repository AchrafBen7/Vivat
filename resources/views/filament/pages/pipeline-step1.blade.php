<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $feeds = $this->getRecentFeeds();
        $items = $this->getRecentItems();
    @endphp

    <style>
        .vp-wrap { display:flex; flex-direction:column; gap:20px; }
        .vp-hero { position:relative; overflow:hidden; border-radius:24px; padding:24px; color:#fff; background:#004241; }
        .vp-hero-inner { position:relative; display:flex; align-items:center; gap:16px; }
        .vp-hero-box { flex-shrink:0; min-width:0; padding:0; border-radius:0; background:transparent; backdrop-filter:none; }
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

        .vp-cols { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        @media(max-width:1024px) { .vp-cols { grid-template-columns:1fr; } }
        .vp-card { overflow:hidden; border-radius:16px; background:#fff; border:1px solid #D6E1DD; }
        .vp-card-head { display:flex; align-items:center; justify-content:space-between; padding:16px 24px; }
        .vp-card-head-left { display:flex; align-items:center; gap:10px; }
        .vp-icon-box { width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:10px; }
        .vp-card-title { font-size:14px; font-weight:600; color:#004241; }
        .vp-pill { display:inline-flex; align-items:center; gap:4px; border-radius:999px; padding:6px 12px; font-size:12px; font-weight:600; background:#EBF1EF; color:#004241; text-decoration:none; transition:background 0.15s; }
        .vp-pill:hover { background:#DEE7E4; }
        .vp-pill svg { width:12px; height:12px; }

        .vp-row { display:flex; align-items:center; gap:14px; padding:14px 24px; transition:background 0.15s; border-top:1px solid #EBF1EF; }
        .vp-row:first-child { border-top:none; }
        .vp-row:hover { background:#F7FAF9; }
        .vp-avatar { flex-shrink:0; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:12px; font-size:11px; font-weight:700; }
        .vp-row-content { min-width:0; flex:1; }
        .vp-row-title { font-size:14px; font-weight:600; color:#004241; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .vp-row-sub { margin-top:2px; font-size:12px; color:rgba(0,66,65,0.45); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .vp-status { flex-shrink:0; display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:600; }
        .vp-dot { width:6px; height:6px; border-radius:50%; display:inline-block; }

        .vp-info { border-radius:16px; padding:20px; background:#FFF0B6; display:flex; align-items:flex-start; gap:12px; }
        .vp-info h4 { font-size:14px; font-weight:600; color:#004241; }
        .vp-info p { margin-top:4px; font-size:12px; color:rgba(0,66,65,0.65); }
        .vp-tip { display:grid; grid-template-columns:42px 1fr; gap:14px; align-items:flex-start; border-radius:16px; padding:18px 20px; background:#F7FAF9; border:1px solid #D6E1DD; }
        .vp-tip-icon { width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:14px; background:#FFF0B6; color:#004241; flex-shrink:0; }
        .vp-tip h4 { font-size:14px; font-weight:700; color:#004241; }
        .vp-tip p { margin-top:4px; font-size:13px; line-height:1.55; color:rgba(0,66,65,0.68); }
    </style>

    <div class="vp-wrap" wire:poll.5s>

        {{-- HERO --}}
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-circle" style="width:112px;height:112px;bottom:-24px;left:-24px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Étape 1</div>
                    <div class="vp-hero-box-title">Sources & repérage</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Les flux RSS sont récupérés plusieurs fois par jour, puis les nouveaux contenus sont préparés pour l'analyse.</p>
                </div>
                <div class="vp-steps" style="display:none" id="vp-steps-desktop">
                    <div class="vp-step vp-step-active">1</div>
                    <div class="vp-step-line"></div>
                    <div class="vp-step vp-step-dim">2</div>
                    <div class="vp-step-line"></div>
                    <div class="vp-step vp-step-dim">3</div>
                </div>
            </div>
        </div>
        <script>
            (function(){var s=document.getElementById('vp-steps-desktop');if(s&&window.innerWidth>=640)s.style.display='flex';
            window.addEventListener('resize',function(){if(s)s.style.display=window.innerWidth>=640?'flex':'none'})})();
        </script>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Le système surveille des sites définis à l'avance, récupère leurs flux RSS, puis repère les nouveaux contenus intéressants pour Vivat. Cette étape sert surtout à collecter la matière première.</p>
            </div>
        </div>

        {{-- STATS --}}
        <div class="vp-stats">
            @foreach ([
                ['label' => 'Sites sources', 'value' => $stats['sources'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Flux actifs', 'value' => $stats['feeds'], 'bg' => '#FFF0B6', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Articles collectés', 'value' => $stats['items_total'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => "Aujourd'hui", 'value' => $stats['items_today'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.6)'],
            ] as $card)
                <div class="vp-stat" style="background:{{ $card['bg'] }}">
                    <div class="vp-stat-val" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="vp-stat-label" style="color:{{ $card['sub'] }}">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- TWO COLUMNS --}}
        <div class="vp-cols">

            {{-- Flux RSS --}}
            <div class="vp-card">
                <div class="vp-card-head">
                    <div class="vp-card-head-left">
                        <div class="vp-icon-box" style="background:#EBF1EF">
                            <svg style="width:16px;height:16px;color:#004241" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.75 19.5v-.75a7.5 7.5 0 00-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                        </div>
                        <span class="vp-card-title">Flux RSS</span>
                    </div>
                    <a href="{{ \App\Filament\Pages\PipelineRssFeeds::getUrl() }}" class="vp-pill">
                        Voir tout
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
                @foreach ($feeds as $feed)
                    <div class="vp-row">
                        <div class="vp-avatar" style="background:#004241;color:#fff">{{ strtoupper(mb_substr($feed['source'], 0, 2)) }}</div>
                        <div class="vp-row-content">
                            <div class="vp-row-title">{{ $feed['source'] }}</div>
                            <div class="vp-row-sub">{{ \Illuminate\Support\Str::limit($feed['url'], 45) }}</div>
                        </div>
                        <div class="vp-status" style="background:#EBF1EF;color:rgba(0,66,65,0.65)">
                            <span class="vp-dot" style="background:#10b981"></span>
                            {{ $feed['last_fetched'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Articles repérés --}}
            <div class="vp-card">
                <div class="vp-card-head">
                    <div class="vp-card-head-left">
                        <div class="vp-icon-box" style="background:#FFF0B6">
                            <svg style="width:16px;height:16px;color:#004241" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        </div>
                        <span class="vp-card-title">Derniers articles repérés</span>
                    </div>
                    <a href="{{ \App\Filament\Pages\PipelineRssItems::getUrl() }}" class="vp-pill">
                        Voir tout
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
                @foreach ($items as $item)
                    @php
                        $sc = match($item['status']) {
                            'enriched' => ['label' => 'Enrichi', 'dot' => '#10b981', 'bg' => '#ecfdf5', 'text' => '#065f46'],
                            'new' => ['label' => 'Nouveau', 'dot' => '#004241', 'bg' => '#EBF1EF', 'text' => '#004241'],
                            'failed' => ['label' => 'Échec', 'dot' => '#ef4444', 'bg' => '#fef2f2', 'text' => '#991b1b'],
                            default => ['label' => $item['status'], 'dot' => '#9ca3af', 'bg' => '#f3f4f6', 'text' => '#4b5563'],
                        };
                    @endphp
                    <div class="vp-row">
                        <div class="vp-avatar" style="background:#EBF1EF">
                            <span class="vp-dot" style="background:{{ $sc['dot'] }};width:10px;height:10px"></span>
                        </div>
                        <div class="vp-row-content">
                            <div class="vp-row-title">{{ $item['title'] }}</div>
                            <div class="vp-row-sub">{{ $item['source'] }} &middot; {{ $item['created_at'] }}</div>
                        </div>
                        <div class="vp-status" style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }}">
                            {{ $sc['label'] }}
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- INFO BANNER --}}
        <div class="vp-info">
            <div class="vp-icon-box" style="background:rgba(0,66,65,0.08);flex-shrink:0">
                <svg style="width:16px;height:16px;color:#004241" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Tout est automatisé</h4>
                <p>Les flux sont récupérés plusieurs fois par jour, les nouveaux contenus sont analysés une fois par jour, puis un brouillon peut être généré automatiquement chaque jour.</p>
            </div>
        </div>

    </div>
</x-filament-panels::page>
