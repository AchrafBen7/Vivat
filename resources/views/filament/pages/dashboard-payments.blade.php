<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $payments = $this->getPayments();
    @endphp

    @include('filament.pages.partials.editorial-page-styles')

    <div class="vp-wrap" wire:poll.5s>
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Editorial</div>
                    <div class="vp-hero-box-title">Paiements</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Retrouve ici les paiements utiles à l'équipe éditoriale, avec un accès rapide aux remboursements et aux soumissions liées.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Tu peux rechercher une transaction, voir son état, ouvrir la soumission associée et rembourser rapidement un paiement si l'article a été refusé.</p>
            </div>
        </div>

        <div class="vp-stats">
            @foreach ([
                ['label' => 'Payés', 'value' => $stats['paid'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => 'Remboursés', 'value' => $stats['refunded'], 'bg' => '#EBF1EF', 'color' => '#004241', 'sub' => 'rgba(0,66,65,0.5)'],
                ['label' => "Aujourd'hui", 'value' => $stats['today'], 'bg' => '#004241', 'color' => '#fff', 'sub' => 'rgba(255,255,255,0.6)'],
            ] as $card)
                <div class="vp-stat" style="background:{{ $card['bg'] }}">
                    <div class="vp-stat-val" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="vp-stat-label" style="color:{{ $card['sub'] }}">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="vp-filters">
            <input type="text" wire:model.live.debounce.300ms="search" class="vp-input" placeholder="Rechercher un paiement..." />
            <select wire:model.live="status" class="vp-select">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="paid">Payé</option>
                <option value="refunded">Remboursé</option>
                <option value="failed">Échoué</option>
                <option value="abandoned">Abandonné</option>
            </select>
        </div>

        @if ($payments === [])
            <div class="vp-empty">Aucun paiement ne correspond à la recherche actuelle.</div>
        @else
            <div class="vp-grid">
                @foreach ($payments as $payment)
                    @php
                        $status = match($payment['status']) {
                            'paid' => ['label' => 'Payé', 'bg' => '#ecfdf5', 'text' => '#065f46'],
                            'refunded' => ['label' => 'Remboursé', 'bg' => '#f3f4f6', 'text' => '#4b5563'],
                            'failed' => ['label' => 'Échoué', 'bg' => '#fef2f2', 'text' => '#991b1b'],
                            'abandoned' => ['label' => 'Abandonné', 'bg' => '#f3f4f6', 'text' => '#4b5563'],
                            default => ['label' => 'En attente', 'bg' => '#fffbeb', 'text' => '#92400e'],
                        };
                    @endphp
                    <section class="vp-card">
                        <div class="vp-card-body">
                            <div class="vp-badges">
                                <span class="vp-badge" style="background:{{ $status['bg'] }};color:{{ $status['text'] }}">{{ $status['label'] }}</span>
                                <span class="vp-badge" style="background:#EBF1EF;color:#004241">{{ $payment['submission_status'] }}</span>
                                <span class="vp-badge" style="background:#FFF0B6;color:#6b5200">{{ $payment['amount'] }}</span>
                            </div>
                            <h3 class="vp-title">{{ $payment['title'] }}</h3>
                            <div class="vp-meta">
                                <span>{{ $payment['created_at'] }}</span>
                            </div>
                            <p class="vp-text">{{ $payment['author'] }}@if($payment['author_email']) · {{ $payment['author_email'] }}@endif</p>
                            @if ($payment['refund_reason'])
                                <div class="vp-topic">Motif du remboursement : {{ $payment['refund_reason'] }}</div>
                            @endif
                            <div class="vp-actions">
                                @if ($payment['submission_url'])
                                    <a href="{{ $payment['submission_url'] }}" class="vp-btn vp-btn-secondary">Voir la soumission</a>
                                @endif
                                @if ($payment['can_refund'])
                                    <button
                                        type="button"
                                        wire:click="refundPayment('{{ $payment['legacy_payment_id'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="refundPayment('{{ $payment['legacy_payment_id'] }}')"
                                        class="vp-btn vp-btn-warning"
                                    >
                                        Rembourser
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
