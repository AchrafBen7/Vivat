<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $feeds = $this->getFeeds();
    @endphp

    <style>
        .vp-wrap { display:flex; flex-direction:column; gap:20px; }
        .vp-hero { position:relative; overflow:hidden; border-radius:24px; padding:24px; color:#fff; background:#004241; }
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

        .vp-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        .vp-stat { border-radius:16px; padding:20px; }
        .vp-stat-val { font-size:30px; font-weight:700; }
        .vp-stat-label { margin-top:4px; font-size:12px; font-weight:500; }

        .vp-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px; }
        .vp-card { overflow:hidden; border-radius:18px; background:#fff; border:1px solid #D6E1DD; }
        .vp-card-body { padding:20px; }
        .vp-badges { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .vp-badge { display:inline-flex; align-items:center; border-radius:10px; padding:4px 10px; font-size:12px; font-weight:700; }
        .vp-title { margin-top:10px; font-size:16px; font-weight:700; line-height:1.3; color:#004241; word-break:break-word; }
        .vp-meta { margin-top:10px; display:flex; flex-direction:column; gap:6px; font-size:12px; color:rgba(0,66,65,0.55); }
        .vp-actions { margin-top:16px; display:flex; align-items:center; gap:10px; }
        .vp-btn { display:inline-flex; align-items:center; justify-content:center; border:none; border-radius:12px; padding:10px 14px; font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; transition:background .15s; }
        .vp-btn-primary { color:#fff; background:#004241; }
        .vp-btn-primary:hover { background:#003130; }
        .vp-btn-secondary { color:#004241; background:#EBF1EF; }
        .vp-btn-secondary:hover { background:#DEE7E4; }
        .vp-empty { border-radius:18px; border:1px dashed #C9D8D4; background:#fff; padding:48px 24px; text-align:center; color:rgba(0,66,65,0.5); }

        @media(max-width:1024px) { .vp-grid, .vp-stats { grid-template-columns:1fr; } }
    </style>

    <div class="vp-wrap">
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Assistant IA</div>
                    <div class="vp-hero-box-title">Flux automatiques</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Cette page regroupe tous les flux surveillés par le système pour repérer de nouveaux contenus.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Chaque flux correspond à une source surveillée automatiquement. Le système les consulte régulièrement pour repérer de nouveaux contenus à analyser ensuite avec l'IA.</p>
            </div>
        </div>

        <div class="vp-stats">
            @foreach ([
                ['label' => 'Flux actifs', 'value' => $stats['active'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Flux inactifs', 'value' => $stats['inactive'], 'bg' => '#FFF0B6', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Items détectés', 'value' => $stats['items'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => "Fetch aujourd'hui", 'value' => $stats['fetched_today'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.6)'],
            ] as $card)
                <div class="vp-stat" style="background:{{ $card['bg'] }}">
                    <div class="vp-stat-val" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="vp-stat-label" style="color:{{ $card['sub'] }}">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        @if ($feeds === [])
            <div class="vp-empty">Aucun flux automatique disponible pour le moment.</div>
        @else
            <div class="vp-grid">
                @foreach ($feeds as $feed)
                    @php
                        $statusBg = $feed['active'] ? '#ecfdf5' : '#f3f4f6';
                        $statusText = $feed['active'] ? '#065f46' : '#4b5563';
                    @endphp
                    <section class="vp-card">
                        <div class="vp-card-body">
                            <div class="vp-badges">
                                <span class="vp-badge" style="background:{{ $statusBg }};color:{{ $statusText }}">
                                    {{ $feed['active'] ? 'Actif' : 'Inactif' }}
                                </span>
                                <span class="vp-badge" style="background:#EBF1EF;color:#004241;font-weight:600">{{ $feed['source'] }}</span>
                                <span class="vp-badge" style="background:#FFF0B6;color:#6b5200">{{ $feed['category'] }}</span>
                            </div>
                            <h3 class="vp-title">{{ $feed['url'] }}</h3>
                            <div class="vp-meta">
                                <div>Rafraîchissement prévu toutes les {{ $feed['interval'] }} minutes</div>
                                <div>{{ $feed['items_count'] }} item(s) repéré(s)</div>
                                <div>Dernier fetch : {{ $feed['last_fetched'] }}</div>
                            </div>
                            <div class="vp-actions">
                                <button
                                    type="button"
                                    wire:click="fetchFeed('{{ $feed['id'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="fetchFeed('{{ $feed['id'] }}')"
                                    class="vp-btn vp-btn-primary"
                                >
                                    Lancer le fetch
                                </button>
                                <a href="{{ $feed['url'] }}" target="_blank" class="vp-btn vp-btn-secondary">Ouvrir</a>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
