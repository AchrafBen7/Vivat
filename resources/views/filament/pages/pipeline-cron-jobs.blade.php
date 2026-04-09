<x-filament-panels::page>
    @php
        $days = $this->getRecentJobsByDay();
        $overview = $this->getOverview();
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
        .vp-stat { border-radius:16px; padding:20px; transition:transform .2s; }
        .vp-stat:hover { transform:translateY(-2px); }
        .vp-stat-val { font-size:30px; font-weight:700; }
        .vp-stat-label { margin-top:4px; font-size:12px; font-weight:500; }
        .vp-card { overflow:hidden; border-radius:16px; background:#fff; border:1px solid #D6E1DD; }
        .vp-card-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 24px; border-bottom:1px solid #EBF1EF; }
        .vp-card-head-left { display:flex; align-items:center; gap:10px; min-width:0; }
        .vp-icon-box { width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:10px; flex-shrink:0; }
        .vp-card-title { font-size:14px; font-weight:700; color:#004241; }
        .vp-card-sub { margin-top:4px; font-size:12px; color:rgba(0,66,65,0.52); }
        .vp-badge { display:inline-flex; align-items:center; border-radius:999px; padding:6px 10px; font-size:11px; font-weight:700; white-space:nowrap; }
        .vp-days { display:flex; flex-direction:column; gap:18px; }
        .vp-day { border-radius:16px; border:1px solid #D6E1DD; background:#fff; overflow:hidden; }
        .vp-day-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:18px 20px; background:#F7FAF9; border-bottom:1px solid #EBF1EF; }
        .vp-day-title { font-size:15px; font-weight:700; color:#004241; }
        .vp-day-sub { margin-top:4px; font-size:12px; color:rgba(0,66,65,0.55); }
        .vp-day-kpis { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .vp-chip { display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:6px 10px; font-size:11px; font-weight:700; background:#EBF1EF; color:#004241; }
        .vp-jobs { display:flex; flex-direction:column; }
        .vp-job { display:grid; grid-template-columns:auto 1fr auto; gap:14px; padding:16px 20px; }
        .vp-job + .vp-job { border-top:1px solid #EBF1EF; }
        .vp-job-dot { width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:999px; font-size:12px; font-weight:700; flex-shrink:0; }
        .vp-job-main { min-width:0; }
        .vp-job-top { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .vp-job-title { font-size:14px; font-weight:700; color:#004241; }
        .vp-job-summary { margin-top:4px; font-size:13px; line-height:1.55; color:rgba(0,66,65,0.76); }
        .vp-job-time { margin-top:6px; font-size:12px; color:rgba(0,66,65,0.45); }
        .vp-detail-list { margin-top:10px; display:flex; flex-wrap:wrap; gap:8px; }
        .vp-detail { display:inline-flex; align-items:center; border-radius:999px; padding:6px 10px; font-size:11px; font-weight:600; background:#F7FAF9; color:#004241; }
        .vp-error { margin-top:10px; border-radius:12px; padding:12px 14px; background:#FEF2F2; color:#991B1B; font-size:12px; line-height:1.55; }
        .vp-empty { padding:40px; text-align:center; border-radius:16px; border:1px dashed #D6E1DD; background:#fff; }
        .vp-empty-text { margin-top:8px; font-size:14px; color:rgba(0,66,65,0.5); }
        @media(max-width:1024px) { .vp-stats { grid-template-columns:repeat(2,1fr); } }
        @media(max-width:640px) {
            .vp-stats { grid-template-columns:1fr; }
            .vp-job { grid-template-columns:1fr; }
            .vp-card-head, .vp-day-head { padding:16px; }
            .vp-job { padding:16px; }
        }
    </style>

    <div class="vp-wrap" wire:poll.10s>
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Assistant IA</div>
                    <div class="vp-hero-box-title">Historique automatique</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Retrouve ici toutes les exécutions du pipeline, classées par jour, pour comprendre ce qui a tourné et ce qui doit être relancé.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Cette page sert d'historique. Elle montre ce qui a réellement tourné dans le pipeline, les résultats obtenus et les éventuels échecs à surveiller.</p>
            </div>
        </div>

        <div class="vp-stats">
            @foreach ([
                ['label' => 'Exécutions suivies', 'value' => $overview['total'], 'bg' => '#FFF0B6', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Réussies', 'value' => $overview['completed'], 'bg' => '#ECFDF5', 'color' => '#065F46', 'sub' => 'rgba(6,95,70,0.55)'],
                ['label' => 'Échecs / relances', 'value' => $overview['failed'] + $overview['retry_scheduled'], 'bg' => '#FEF2F2', 'color' => '#991B1B', 'sub' => 'rgba(153,27,27,0.55)'],
                ['label' => 'Jours couverts', 'value' => $overview['days'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.62)'],
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
                    <div class="vp-icon-box" style="background:#EBF1EF">
                        <svg style="width:16px;height:16px;color:#004241" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="vp-card-title">Journal des exécutions</div>
                        <div class="vp-card-sub">
                            @if ($overview['last_label'])
                                Dernière activité : {{ $overview['last_label'] }} le {{ $overview['last_time'] }}.
                            @else
                                Aucune exécution enregistrée pour le moment.
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($days === [])
                <div class="vp-empty">
                    <svg style="width:40px;height:40px;color:#004241;margin:0 auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="vp-empty-text">Aucun historique disponible pour le moment. Les premières lignes apparaîtront dès que l'automatisation ou une relance manuelle se lance.</p>
                </div>
            @else
                <div style="padding:20px">
                    <div class="vp-days">
                        @foreach ($days as $day)
                            @php
                                $completed = collect($day['jobs'])->where('status', 'completed')->count();
                                $failed = collect($day['jobs'])->where('status', 'failed')->count();
                                $running = collect($day['jobs'])->where('status', 'running')->count();
                                $retry = collect($day['jobs'])->filter(fn ($job) => ($job['metadata']['retry_scheduled'] ?? false) === true)->count();
                            @endphp
                            <section class="vp-day">
                                <div class="vp-day-head">
                                    <div>
                                        <div class="vp-day-title">{{ $day['date'] }}</div>
                                        <div class="vp-day-sub">{{ count($day['jobs']) }} exécution{{ count($day['jobs']) > 1 ? 's' : '' }} enregistrée{{ count($day['jobs']) > 1 ? 's' : '' }} ce jour-là.</div>
                                    </div>
                                    <div class="vp-day-kpis">
                                        <span class="vp-chip">{{ $completed }} réussi{{ $completed > 1 ? 's' : '' }}</span>
                                        @if ($running > 0)
                                            <span class="vp-chip" style="background:#EFF6FF;color:#1D4ED8">{{ $running }} en cours</span>
                                        @endif
                                        @if ($failed > 0)
                                            <span class="vp-chip" style="background:#FEF2F2;color:#991B1B">{{ $failed }} échec{{ $failed > 1 ? 's' : '' }}</span>
                                        @endif
                                        @if ($retry > 0)
                                            <span class="vp-chip" style="background:#FFFBEB;color:#92400E">{{ $retry }} relance{{ $retry > 1 ? 's' : '' }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="vp-jobs">
                                    @foreach ($day['jobs'] as $job)
                                        @php
                                            $styles = match(true) {
                                                ($job['metadata']['retry_scheduled'] ?? false) === true => ['dot_bg' => '#FFFBEB', 'dot_text' => '#92400E', 'badge_bg' => '#FFFBEB', 'badge_text' => '#92400E', 'icon' => '↺'],
                                                $job['status'] === 'completed' => ['dot_bg' => '#ECFDF5', 'dot_text' => '#065F46', 'badge_bg' => '#ECFDF5', 'badge_text' => '#065F46', 'icon' => '✓'],
                                                $job['status'] === 'failed' => ['dot_bg' => '#FEF2F2', 'dot_text' => '#991B1B', 'badge_bg' => '#FEF2F2', 'badge_text' => '#991B1B', 'icon' => '!'],
                                                $job['status'] === 'running' => ['dot_bg' => '#EFF6FF', 'dot_text' => '#1D4ED8', 'badge_bg' => '#EFF6FF', 'badge_text' => '#1D4ED8', 'icon' => '…'],
                                                default => ['dot_bg' => '#EBF1EF', 'dot_text' => '#004241', 'badge_bg' => '#EBF1EF', 'badge_text' => '#004241', 'icon' => '•'],
                                            };
                                        @endphp
                                        <div class="vp-job">
                                            <div class="vp-job-dot" style="background:{{ $styles['dot_bg'] }}; color:{{ $styles['dot_text'] }}">
                                                {{ $styles['icon'] }}
                                            </div>
                                            <div class="vp-job-main">
                                                <div class="vp-job-top">
                                                    <div class="vp-job-title">{{ $job['label'] }}</div>
                                                    <span class="vp-badge" style="background:{{ $styles['badge_bg'] }}; color:{{ $styles['badge_text'] }}">{{ $job['status_label'] }}</span>
                                                </div>
                                                <div class="vp-job-summary">{{ $job['summary'] }}</div>
                                                <div class="vp-job-time">
                                                    Début {{ $job['started_at'] ?? '' }} · Fin {{ $job['completed_at'] ?? '' }}
                                                </div>

                                                @if (!empty($job['details']))
                                                    <div class="vp-detail-list">
                                                        @foreach ($job['details'] as $detail)
                                                            <span class="vp-detail">{{ $detail }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if (!empty($job['error_message']))
                                                    <div class="vp-error">{{ $this->shortError($job['error_message']) }}</div>
                                                @endif
                                            </div>
                                            <div></div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
