<x-filament-panels::page>
    @php
        $todayAutomation = $this->getTodayAutomation();
        $generationOverlay = $this->getGenerationOverlayState();
    @endphp

    <style>
        .vp-wrap { display:flex; flex-direction:column; gap:20px; }
        .vp-hero { position:relative; overflow:hidden; border-radius:24px; padding:24px; color:#fff; background:#004241; }
        .vp-hero-inner { position:relative; display:flex; align-items:center; gap:16px; }
        .vp-hero-box { flex-shrink:0; min-width:180px; padding:12px 16px; border-radius:16px; background:rgba(255,255,255,0.12); backdrop-filter:blur(8px); }
        .vp-hero-box-step { font-size:11px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:rgba(255,255,255,0.72); }
        .vp-hero-box-title { margin-top:4px; font-size:18px; font-weight:700; line-height:1.1; }
        .vp-hero-text p { margin-top:4px; font-size:14px; color:rgba(255,255,255,0.65); }
        .vp-hero-circle { position:absolute; border-radius:50%; background:rgba(255,255,255,0.05); pointer-events:none; }
        .vp-tip { display:grid; grid-template-columns:42px 1fr; gap:14px; align-items:flex-start; border-radius:16px; padding:18px 20px; background:#F7FAF9; border:1px solid #D6E1DD; }
        .vp-tip-icon { width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:14px; background:#FFF0B6; color:#004241; flex-shrink:0; }
        .vp-tip h4 { font-size:14px; font-weight:700; color:#004241; }
        .vp-tip p { margin-top:4px; font-size:13px; line-height:1.55; color:rgba(0,66,65,0.68); }
        .vp-journey { display:grid; grid-template-columns:1.1fr .9fr; gap:20px; }
        .vp-journey-box { border-radius:16px; border:1px solid #D6E1DD; background:#fff; padding:20px; }
        .vp-journey-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .vp-journey-head-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
        .vp-journey-title { font-size:15px; font-weight:700; color:#004241; }
        .vp-journey-sub { margin-top:4px; font-size:13px; color:rgba(0,66,65,0.58); }
        .vp-journey-steps { margin-top:18px; display:flex; flex-direction:column; gap:12px; }
        .vp-journey-step { display:flex; align-items:flex-start; gap:12px; border-radius:14px; background:#F7FAF9; padding:14px; }
        .vp-journey-dot { width:28px; height:28px; border-radius:999px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:12px; font-weight:700; }
        .vp-journey-step-main { min-width:0; flex:1; }
        .vp-journey-step-head { display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .vp-journey-step-title { font-size:13px; font-weight:700; color:#004241; }
        .vp-journey-step-text { margin-top:3px; font-size:12px; line-height:1.55; color:rgba(0,66,65,0.62); }
        .vp-status-badge { display:inline-flex; align-items:center; border-radius:999px; padding:6px 10px; font-size:11px; font-weight:700; white-space:nowrap; }
        .vp-mini-btn { display:inline-flex; align-items:center; justify-content:center; border:none; border-radius:10px; padding:7px 10px; font-size:11px; font-weight:700; color:#004241; background:#EBF1EF; cursor:pointer; transition:background .15s; white-space:nowrap; }
        .vp-mini-btn:hover { background:#DEE7E4; }
        .vp-mini-btn[disabled] { opacity:.7; cursor:wait; }
        .vp-journey-kpis { margin-top:18px; display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
        .vp-journey-kpi { border-radius:14px; background:#F7FAF9; padding:14px; }
        .vp-journey-kpi-value { font-size:24px; font-weight:700; color:#004241; }
        .vp-journey-kpi-label { margin-top:3px; font-size:11px; letter-spacing:.04em; text-transform:uppercase; color:rgba(0,66,65,0.45); }
        .vp-journey-highlight { margin-top:18px; border-radius:14px; padding:16px; background:#EBF1EF; }
        .vp-journey-highlight-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:rgba(0,66,65,0.45); }
        .vp-journey-highlight-main { margin-top:8px; font-size:16px; font-weight:700; color:#004241; }
        .vp-journey-highlight-sub { margin-top:4px; font-size:12px; color:rgba(0,66,65,0.62); }
        .vp-journey-link { margin-top:12px; display:inline-flex; align-items:center; gap:6px; text-decoration:none; color:#004241; font-size:12px; font-weight:700; }
        .vp-overlay { position:fixed; inset:0; z-index:9999; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.96); }
        .vp-overlay-close { position:absolute; top:24px; left:24px; width:44px; height:44px; border:none; border-radius:999px; background:#EBF1EF; color:#004241; display:flex; align-items:center; justify-content:center; cursor:pointer; }
        .vp-overlay-card { width:min(640px, calc(100vw - 48px)); text-align:center; }
        .vp-overlay-label { font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:rgba(0,66,65,.45); }
        .vp-overlay-title { margin-top:16px; font-size:32px; font-weight:700; line-height:1.15; color:#004241; }
        .vp-overlay-progress { margin:28px auto 0; width:100%; height:16px; border-radius:999px; background:#EBF1EF; overflow:hidden; }
        .vp-overlay-progress-fill { height:100%; border-radius:999px; background:#004241; transition:width .35s ease; }
        .vp-overlay-percent { margin-top:16px; font-size:28px; font-weight:700; color:#004241; }
        .vp-overlay-sub { margin-top:10px; font-size:14px; line-height:1.6; color:rgba(0,66,65,.62); }
        .vp-overlay-link { margin-top:18px; display:inline-flex; align-items:center; gap:8px; text-decoration:none; font-size:13px; font-weight:700; color:#004241; }
        @media(max-width:1024px) { .vp-journey { grid-template-columns:1fr; } }
    </style>

    <div class="vp-wrap" wire:poll.5s>
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Assistant IA</div>
                    <div class="vp-hero-box-title">Suivi du jour</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Visualise ici l’état de l’automatisation du jour et relance une étape si nécessaire.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu’il se passe ici</h4>
                <p>Cette page sert de monitoring quotidien. Elle te dit immédiatement si l’article du jour a bien été généré et où la chaîne s’est éventuellement arrêtée.</p>
            </div>
        </div>

        <div class="vp-journey">
            <div class="vp-journey-box">
                @php
                    $globalColors = match($todayAutomation['summary']['global_status']) {
                        'paused' => ['label' => 'En pause', 'bg' => '#f3f4f6', 'text' => '#4b5563'],
                        'done' => ['label' => 'Succès', 'bg' => '#ecfdf5', 'text' => '#065f46'],
                        'running' => ['label' => 'En cours', 'bg' => '#eff6ff', 'text' => '#1d4ed8'],
                        'failed' => ['label' => 'À relancer', 'bg' => '#fef2f2', 'text' => '#991b1b'],
                        default => ['label' => 'En attente', 'bg' => '#EBF1EF', 'text' => '#004241'],
                    };
                @endphp
                <div class="vp-journey-head">
                    <div>
                        <div class="vp-journey-title">Automatisation du jour</div>
                        <div class="vp-journey-sub">Vue synthétique des étapes automatiques de la journée.</div>
                    </div>
                    <div class="vp-journey-head-actions">
                        <button type="button" wire:click="rerunFullFlow" wire:loading.attr="disabled" wire:target="rerunFullFlow" class="vp-mini-btn">
                            Relancer tout le flux
                        </button>
                        @if ($todayAutomation['summary']['automation_paused'])
                            <button type="button" wire:click="resumeAutomation" wire:loading.attr="disabled" wire:target="resumeAutomation" class="vp-mini-btn">Reprendre</button>
                        @else
                            <button type="button" wire:click="pauseAutomation" wire:loading.attr="disabled" wire:target="pauseAutomation" class="vp-mini-btn">Pauser l’automatisation</button>
                        @endif
                        <span class="vp-status-badge" style="background:{{ $globalColors['bg'] }}; color:{{ $globalColors['text'] }}">{{ $globalColors['label'] }}</span>
                    </div>
                </div>

                <div class="vp-journey-steps">
                    @foreach ($todayAutomation['steps'] as $index => $step)
                        @php
                            $stepColors = match($step['status']) {
                                'done' => ['bg' => '#ecfdf5', 'text' => '#065f46', 'icon' => '✓'],
                                'running' => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'icon' => '…'],
                                'failed' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'icon' => '!'],
                                default => ['bg' => '#EBF1EF', 'text' => '#004241', 'icon' => (string) ($index + 1)],
                            };
                            $stepBadge = match($step['status']) {
                                'done' => ['label' => 'Passé', 'bg' => '#ecfdf5', 'text' => '#065f46'],
                                'running' => ['label' => 'En cours', 'bg' => '#eff6ff', 'text' => '#1d4ed8'],
                                'failed' => ['label' => 'Raté', 'bg' => '#fef2f2', 'text' => '#991b1b'],
                                default => ['label' => 'En attente', 'bg' => '#EBF1EF', 'text' => '#004241'],
                            };
                        @endphp
                        <div class="vp-journey-step">
                            <div class="vp-journey-dot" style="background:{{ $stepColors['bg'] }}; color:{{ $stepColors['text'] }}">{{ $stepColors['icon'] }}</div>
                            <div class="vp-journey-step-main">
                                <div class="vp-journey-step-head">
                                    <div class="vp-journey-step-title">{{ $step['label'] }}</div>
                                    <span class="vp-status-badge" style="background:{{ $stepBadge['bg'] }}; color:{{ $stepBadge['text'] }}">{{ $stepBadge['label'] }}</span>
                                </div>
                                <div class="vp-journey-step-text">{{ $step['description'] }}</div>
                                <div style="margin-top:10px">
                                    <button type="button" wire:click="{{ $step['action'] }}" wire:loading.attr="disabled" wire:target="{{ $step['action'] }}" class="vp-mini-btn">{{ $step['action_label'] }}</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="vp-journey-box">
                <div class="vp-journey-title">Résumé rapide</div>
                <div class="vp-journey-sub">
                    @if (!empty($todayAutomation['summary']['last_update']))
                        Dernière mise à jour {{ $todayAutomation['summary']['last_update'] }}.
                    @else
                        Aucune activité enregistrée aujourd’hui pour le moment.
                    @endif
                </div>

                <div class="vp-journey-kpis">
                    <div class="vp-journey-kpi">
                        <div class="vp-journey-kpi-value">{{ $todayAutomation['summary']['new_items'] }}</div>
                        <div class="vp-journey-kpi-label">Articles repérés</div>
                    </div>
                    <div class="vp-journey-kpi">
                        <div class="vp-journey-kpi-value">{{ $todayAutomation['summary']['enriched_items'] }}</div>
                        <div class="vp-journey-kpi-label">Analyses IA</div>
                    </div>
                    <div class="vp-journey-kpi">
                        <div class="vp-journey-kpi-value">{{ $todayAutomation['summary']['proposal_count'] }}</div>
                        <div class="vp-journey-kpi-label">Idées prêtes</div>
                    </div>
                    <div class="vp-journey-kpi">
                        <div class="vp-journey-kpi-value">{{ $todayAutomation['summary']['generate_runs'] }}</div>
                        <div class="vp-journey-kpi-label">Générations</div>
                    </div>
                </div>

                <div class="vp-journey-highlight">
                    <div class="vp-journey-highlight-title">Article du jour</div>
                    @if ($todayAutomation['summary']['article_generated'])
                        <div class="vp-journey-highlight-main">{{ $todayAutomation['summary']['article_title'] }}</div>
                        <div class="vp-journey-highlight-sub">Statut : {{ $todayAutomation['summary']['article_status'] === 'published' ? 'Publié' : 'Brouillon' }}</div>
                        @if (!empty($todayAutomation['summary']['article_preview_url']))
                            <a href="{{ $todayAutomation['summary']['article_preview_url'] }}" target="_blank" class="vp-journey-link">
                                Ouvrir l’aperçu
                                <svg style="width:12px;height:12px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        @endif
                    @else
                        <div class="vp-journey-highlight-main">Aucun brouillon généré aujourd’hui</div>
                        <div class="vp-journey-highlight-sub">La chaîne n’est pas encore allée jusqu’à la dernière étape.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($generationOverlay['visible'])
        <div class="vp-overlay">
            <button type="button" wire:click="closeGenerationOverlay" class="vp-overlay-close" aria-label="Annuler l’affichage">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="vp-overlay-card">
                <div class="vp-overlay-label">{{ $generationOverlay['eyebrow'] }}</div>
                <div class="vp-overlay-title">{{ $generationOverlay['headline'] }}</div>
                <div class="vp-overlay-progress">
                    <div class="vp-overlay-progress-fill" style="width: {{ max(0, min(100, (int) $generationOverlay['progress'])) }}%"></div>
                </div>
                <div class="vp-overlay-percent">{{ $generationOverlay['label'] }}</div>
                <div class="vp-overlay-sub">
                    @if ($generationOverlay['is_failed'])
                        Tu peux fermer cet écran puis relancer la génération si nécessaire.
                    @elseif ($generationOverlay['is_finished'])
                        La création est terminée. Tu peux ouvrir l’aperçu ou fermer cet écran.
                    @else
                        L’écran se met à jour automatiquement jusqu’à la fin de la génération.
                    @endif
                </div>

                @if (!empty($generationOverlay['article_preview_url']))
                    <a href="{{ $generationOverlay['article_preview_url'] }}" target="_blank" class="vp-overlay-link">
                        Ouvrir l’aperçu
                        <svg style="width:12px;height:12px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
