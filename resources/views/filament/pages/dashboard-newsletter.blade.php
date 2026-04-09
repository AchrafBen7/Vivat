<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $subscribers = $this->getSubscribers();
    @endphp

    @include('filament.pages.partials.editorial-page-styles')

    <div class="vp-wrap" wire:poll.5s>
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Editorial</div>
                    <div class="vp-hero-box-title">Newsletter</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Retrouve ici les abonnés newsletter, leur état d'inscription et les profils à nettoyer ou désinscrire si besoin.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Tu peux filtrer les abonnés, retrouver leur état d'inscription et désinscrire rapidement une adresse si nécessaire.</p>
            </div>
        </div>

        <div class="vp-stats">
            @foreach ([
                ['label' => 'Actifs', 'value' => $stats['active'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'En attente', 'value' => $stats['pending'], 'bg' => '#FFF0B6', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Désinscrits', 'value' => $stats['unsubscribed'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => "Aujourd'hui", 'value' => $stats['today'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.6)'],
            ] as $card)
                <div class="vp-stat" style="background:{{ $card['bg'] }}">
                    <div class="vp-stat-val" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="vp-stat-label" style="color:{{ $card['sub'] }}">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="vp-filters">
            <input type="text" wire:model.live.debounce.300ms="search" class="vp-input" placeholder="Rechercher un abonné..." />
            <select wire:model.live="state" class="vp-select">
                <option value="">Tous les états</option>
                <option value="active">Confirmés</option>
                <option value="pending">En attente</option>
                <option value="unsubscribed">Désinscrits</option>
            </select>
        </div>

        @if ($subscribers === [])
            <div class="vp-empty">Aucun abonné ne correspond à la recherche actuelle.</div>
        @else
            <div class="vp-grid">
                @foreach ($subscribers as $subscriber)
                    @php
                        $status = match($subscriber['status']) {
                            'active' => ['label' => 'Confirmé', 'bg' => '#ecfdf5', 'text' => '#065f46'],
                            'unsubscribed' => ['label' => 'Désinscrit', 'bg' => '#fef2f2', 'text' => '#991b1b'],
                            default => ['label' => 'En attente', 'bg' => '#fffbeb', 'text' => '#92400e'],
                        };
                    @endphp
                    <section class="vp-card">
                        <div class="vp-card-body">
                            <div class="vp-badges">
                                <span class="vp-badge" style="background:{{ $status['bg'] }};color:{{ $status['text'] }}">{{ $status['label'] }}</span>
                                <span class="vp-badge" style="background:#EBF1EF;color:#004241">{{ $subscriber['joined_at'] }}</span>
                            </div>
                            <h3 class="vp-title">{{ $subscriber['email'] }}</h3>
                            <div class="vp-meta">
                                @if ($subscriber['name'] !== '')
                                    <span>{{ $subscriber['name'] }}</span>
                                @endif
                                @if ($subscriber['confirmed_at'])
                                    <span>•</span>
                                    <span>Confirmé le {{ $subscriber['confirmed_at'] }}</span>
                                @elseif ($subscriber['unsubscribed_at'])
                                    <span>•</span>
                                    <span>Désinscrit le {{ $subscriber['unsubscribed_at'] }}</span>
                                @endif
                            </div>
                            <p class="vp-text">
                                @if ($subscriber['interests'] !== [])
                                    Centres d'intérêt : {{ implode(', ', $subscriber['interests']) }}
                                @else
                                    Aucun centre d'intérêt renseigné.
                                @endif
                            </p>
                            <div class="vp-actions">
                                @if ($subscriber['can_unsubscribe'])
                                    <button
                                        type="button"
                                        wire:click="unsubscribeSubscriber('{{ $subscriber['id'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="unsubscribeSubscriber('{{ $subscriber['id'] }}')"
                                        class="vp-btn vp-btn-warning"
                                    >
                                        Désinscrire
                                    </button>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
