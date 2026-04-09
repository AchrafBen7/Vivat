<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $items = $this->getItems();
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

        .vp-filters { display:grid; grid-template-columns:1fr; gap:16px; }
        .vp-select { width:100%; border:1px solid #D6E1DD; border-radius:14px; padding:12px 14px; font-size:14px; color:#004241; background:#fff; }
        .vp-select:focus { outline:none; border-color:#4C807C; box-shadow:0 0 0 3px rgba(76,128,124,0.12); }

        .vp-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px; }
        .vp-card { overflow:hidden; border-radius:18px; background:#fff; border:1px solid #D6E1DD; }
        .vp-card-body { padding:20px; }
        .vp-badges { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .vp-badge { display:inline-flex; align-items:center; border-radius:10px; padding:4px 10px; font-size:12px; font-weight:700; }
        .vp-title { margin-top:10px; font-size:17px; font-weight:700; line-height:1.3; color:#004241; }
        .vp-meta { margin-top:8px; display:flex; align-items:center; flex-wrap:wrap; gap:8px; font-size:12px; color:rgba(0,66,65,0.45); }
        .vp-text { margin-top:10px; font-size:13px; line-height:1.6; color:rgba(0,66,65,0.62); }
        .vp-topic { margin-top:12px; border-radius:12px; padding:10px 12px; background:#F7FAF9; font-size:12px; color:#004241; }
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
                    <div class="vp-hero-box-title">Articles repérés</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Cette page liste les contenus récupérés par les flux avant leur enrichissement par l'IA.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Chaque carte correspond à un contenu détecté automatiquement depuis un flux. Avant de devenir une idée d'article, ce contenu doit encore être enrichi, résumé et évalué par l'IA.</p>
            </div>
        </div>

        <div class="vp-stats">
            @foreach ([
                ['label' => 'Nouveaux', 'value' => $stats['new'], 'bg' => '#FFF0B6', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'En cours', 'value' => $stats['enriching'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Enrichis', 'value' => $stats['enriched'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => "Aujourd'hui", 'value' => $stats['today'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.6)'],
            ] as $card)
                <div class="vp-stat" style="background:{{ $card['bg'] }}">
                    <div class="vp-stat-val" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="vp-stat-label" style="color:{{ $card['sub'] }}">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="vp-filters">
            <select wire:model.live="enrichmentFilter" class="vp-select">
                <option value="">Tous les articles repérés</option>
                <option value="enriched">Seulement enrichis</option>
                <option value="not_enriched">Pas encore enrichis</option>
            </select>
        </div>

        @if ($items === [])
            <div class="vp-empty">Aucun article repéré pour le moment.</div>
        @else
            <div class="vp-grid">
                @foreach ($items as $item)
                    @php
                        $status = match($item['status']) {
                            'new' => ['label' => 'Nouveau', 'bg' => '#EBF1EF', 'text' => '#004241'],
                            'enriching' => ['label' => 'Enrichissement', 'bg' => '#fffbeb', 'text' => '#92400e'],
                            'enriched' => ['label' => 'Enrichi', 'bg' => '#ecfdf5', 'text' => '#065f46'],
                            'used' => ['label' => 'Utilisé', 'bg' => '#dbeafe', 'text' => '#1d4ed8'],
                            'failed' => ['label' => 'Échec', 'bg' => '#fef2f2', 'text' => '#991b1b'],
                            default => ['label' => ucfirst($item['status']), 'bg' => '#f3f4f6', 'text' => '#4b5563'],
                        };
                    @endphp
                    <section class="vp-card">
                        <div class="vp-card-body">
                            <div class="vp-badges">
                                <span class="vp-badge" style="background:{{ $status['bg'] }};color:{{ $status['text'] }}">{{ $status['label'] }}</span>
                                <span class="vp-badge" style="background:#EBF1EF;color:#004241;font-weight:600">{{ $item['source'] }}</span>
                                <span class="vp-badge" style="background:#FFF0B6;color:#6b5200">{{ $item['category'] }}</span>
                            </div>
                            <h3 class="vp-title">{{ $item['title'] }}</h3>
                            <div class="vp-meta">
                                <span>Publié : {{ $item['published_at'] }}</span>
                                <span>•</span>
                                <span>Repéré : {{ $item['fetched_at'] }}</span>
                            </div>
                            <p class="vp-text">{{ $item['description'] }}</p>
                            <div class="vp-topic">Sujet IA : {{ $item['topic'] }}</div>
                            <div class="vp-actions">
                                @if ($item['can_enrich'])
                                    <button
                                        type="button"
                                        wire:click="enrichItem('{{ $item['id'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="enrichItem('{{ $item['id'] }}')"
                                        class="vp-btn vp-btn-primary"
                                    >
                                        Enrichir
                                    </button>
                                @endif
                                @if (!empty($item['url']))
                                    <a href="{{ $item['url'] }}" target="_blank" class="vp-btn vp-btn-secondary">Voir la source</a>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
