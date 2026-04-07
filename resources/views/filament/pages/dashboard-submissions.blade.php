<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $submissions = $this->getSubmissions();
    @endphp

    <style>
        .vp-wrap { display:flex; flex-direction:column; gap:20px; }
        .vp-hero { position:relative; overflow:hidden; border-radius:24px; padding:24px; color:#fff; background:#004241; }
        .vp-hero-inner { position:relative; display:flex; align-items:center; gap:16px; }
        .vp-hero-box { flex-shrink:0; min-width:180px; padding:12px 16px; border-radius:16px; background:rgba(255,255,255,0.12); backdrop-filter:blur(8px); }
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

        .vp-filters { display:grid; grid-template-columns:2fr 1fr; gap:16px; }
        .vp-input, .vp-select { width:100%; border:1px solid #D6E1DD; border-radius:14px; padding:12px 14px; font-size:14px; color:#004241; background:#fff; }
        .vp-input:focus, .vp-select:focus { outline:none; border-color:#4C807C; box-shadow:0 0 0 3px rgba(76,128,124,0.12); }

        .vp-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px; }
        .vp-card { overflow:hidden; border-radius:18px; background:#fff; border:1px solid #D6E1DD; }
        .vp-cover { width:100%; height:210px; object-fit:cover; display:block; background:#EBF1EF; }
        .vp-card-body { padding:20px; }
        .vp-badges { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .vp-badge { display:inline-flex; align-items:center; border-radius:10px; padding:4px 10px; font-size:12px; font-weight:700; }
        .vp-title { margin-top:10px; font-size:17px; font-weight:700; line-height:1.3; color:#004241; }
        .vp-meta { margin-top:8px; display:flex; align-items:center; flex-wrap:wrap; gap:8px; font-size:12px; color:rgba(0,66,65,0.45); }
        .vp-text { margin-top:10px; font-size:13px; line-height:1.6; color:rgba(0,66,65,0.62); }
        .vp-author { margin-top:12px; border-radius:12px; padding:10px 12px; background:#F7FAF9; font-size:12px; color:#004241; }
        .vp-actions { margin-top:16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .vp-btn { display:inline-flex; align-items:center; justify-content:center; border:none; border-radius:12px; padding:10px 14px; font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; transition:background .15s; }
        .vp-btn-primary { color:#fff; background:#004241; }
        .vp-btn-primary:hover { background:#003130; }
        .vp-btn-secondary { color:#004241; background:#EBF1EF; }
        .vp-btn-secondary:hover { background:#DEE7E4; }
        .vp-empty { border-radius:18px; border:1px dashed #C9D8D4; background:#fff; padding:48px 24px; text-align:center; color:rgba(0,66,65,0.5); }

        @media(max-width:1024px) { .vp-grid, .vp-stats, .vp-filters { grid-template-columns:1fr; } }
    </style>

    <div class="vp-wrap" wire:poll.5s>
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Tableau de bord</div>
                    <div class="vp-hero-box-title">Soumissions</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Retrouve ici les articles envoyés par les rédacteurs avant validation, refus ou publication finale.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu’il se passe ici</h4>
                <p>Tu peux filtrer les soumissions, ouvrir leur aperçu, puis passer à la modération détaillée pour les approuver ou les refuser.</p>
            </div>
        </div>

        <div class="vp-stats">
            @foreach ([
                ['label' => 'En attente', 'value' => $stats['pending'], 'bg' => '#FFF0B6', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Approuvées', 'value' => $stats['approved'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Rejetées', 'value' => $stats['rejected'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => "Aujourd'hui", 'value' => $stats['today'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.6)'],
            ] as $card)
                <div class="vp-stat" style="background:{{ $card['bg'] }}">
                    <div class="vp-stat-val" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="vp-stat-label" style="color:{{ $card['sub'] }}">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="vp-filters">
            <input type="text" wire:model.live.debounce.300ms="search" class="vp-input" placeholder="Rechercher une soumission..." />
            <select wire:model.live="status" class="vp-select">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="approved">Approuvée</option>
                <option value="rejected">Rejetée</option>
            </select>
        </div>

        @if ($submissions === [])
            <div class="vp-empty">Aucune soumission ne correspond à la recherche actuelle.</div>
        @else
            <div class="vp-grid">
                @foreach ($submissions as $submission)
                    @php
                        $status = match($submission['status']) {
                            'approved' => ['label' => 'Approuvée', 'bg' => '#ecfdf5', 'text' => '#065f46'],
                            'rejected' => ['label' => 'Rejetée', 'bg' => '#fef2f2', 'text' => '#991b1b'],
                            default => ['label' => 'En attente', 'bg' => '#fffbeb', 'text' => '#92400e'],
                        };
                        $paymentLabel = match($submission['payment_status']) {
                            'paid' => 'Payé',
                            'pending' => 'Paiement en attente',
                            'refunded' => 'Remboursé',
                            'failed' => 'Échoué',
                            default => null,
                        };
                    @endphp
                    <section class="vp-card">
                        @if (!empty($submission['cover']))
                            <img src="{{ $submission['cover'] }}" alt="" class="vp-cover">
                        @endif
                        <div class="vp-card-body">
                            <div class="vp-badges">
                                <span class="vp-badge" style="background:{{ $status['bg'] }};color:{{ $status['text'] }}">{{ $status['label'] }}</span>
                                <span class="vp-badge" style="background:#EBF1EF;color:#004241;font-weight:600">{{ $submission['category'] }}</span>
                                @if ($paymentLabel)
                                    <span class="vp-badge" style="background:#EBF1EF;color:#004241">{{ $paymentLabel }}</span>
                                @endif
                            </div>
                            <h3 class="vp-title">{{ $submission['title'] }}</h3>
                            <div class="vp-meta">
                                <span>{{ $submission['reading_time'] }} min</span>
                                <span>•</span>
                                <span>{{ $submission['created_at'] }}</span>
                                @if ($submission['reviewer'])
                                    <span>•</span>
                                    <span>Par {{ $submission['reviewer'] }}</span>
                                @endif
                            </div>
                            <p class="vp-text">{{ $submission['excerpt'] }}</p>
                            <div class="vp-author">
                                <strong>{{ $submission['author'] }}</strong>
                                @if ($submission['author_email'])
                                    <span> · {{ $submission['author_email'] }}</span>
                                @endif
                                @if ($submission['payment_amount'])
                                    <div style="margin-top:4px;color:rgba(0,66,65,0.55)">Paiement : {{ $submission['payment_amount'] }}</div>
                                @endif
                            </div>
                            <div class="vp-actions">
                                <a href="{{ $submission['preview_url'] }}" target="_blank" class="vp-btn vp-btn-primary">Aperçu</a>
                                <a href="{{ $submission['moderate_url'] }}" class="vp-btn vp-btn-secondary">Modérer</a>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
