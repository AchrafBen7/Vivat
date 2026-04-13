<x-filament-panels::page>
    @php
        $submission = $this->getSubmissionData();

        $status = match($submission['status']) {
            'submitted', 'pending' => ['label' => 'Soumise', 'bg' => '#fffbeb', 'text' => '#92400e'],
            'under_review' => ['label' => 'En revue', 'bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'changes_requested' => ['label' => 'Corrections demandées', 'bg' => '#fff7ed', 'text' => '#c2410c'],
            'price_proposed' => ['label' => 'Prix proposé', 'bg' => '#ecfeff', 'text' => '#0f766e'],
            'awaiting_payment', 'payment_pending' => ['label' => 'En attente de paiement', 'bg' => '#fffbeb', 'text' => '#92400e'],
            'payment_succeeded' => ['label' => 'Paiement reçu', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            'payment_failed', 'payment_canceled', 'payment_expired', 'rejected' => ['label' => 'Action requise', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'payment_refunded' => ['label' => 'Remboursée', 'bg' => '#f3f4f6', 'text' => '#4b5563'],
            'published', 'approved' => ['label' => 'Publiée', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            default => ['label' => ucfirst((string) $submission['status']), 'bg' => '#EBF1EF', 'text' => '#004241'],
        };

        $paymentStatus = match($submission['payment_status']) {
            'pending' => ['label' => 'En attente', 'bg' => '#fffbeb', 'text' => '#92400e'],
            'processing' => ['label' => 'En cours', 'bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'succeeded' => ['label' => 'Payé', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            'failed' => ['label' => 'Échoué', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'canceled' => ['label' => 'Annulé', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'expired' => ['label' => 'Expiré', 'bg' => '#fff7ed', 'text' => '#c2410c'],
            'refunded' => ['label' => 'Remboursé', 'bg' => '#f3f4f6', 'text' => '#4b5563'],
            'disputed' => ['label' => 'Litige', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            default => null,
        };

        $quoteStatus = match($submission['quote_status']) {
            'sent' => ['label' => 'Envoyée', 'bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'accepted' => ['label' => 'Acceptée', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            'expired' => ['label' => 'Expirée', 'bg' => '#fff7ed', 'text' => '#c2410c'],
            'canceled' => ['label' => 'Annulée', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            default => null,
        };

        $articleType = match($submission['quote_type']) {
            'hot_news' => 'Hot news',
            'long_form' => 'Long format',
            'standard' => 'Standard',
            default => null,
        };

        $nextStep = match($submission['status']) {
            'submitted', 'pending' => 'Démarrer la revue',
            'under_review' => 'Décider : corrections, rejet ou proposition de prix',
            'changes_requested' => 'Attendre un nouvel envoi du rédacteur',
            'price_proposed', 'awaiting_payment' => 'Attendre le paiement du rédacteur',
            'payment_pending' => 'Paiement en cours de validation',
            'payment_succeeded' => 'Publication ou finalisation automatique',
            'published', 'approved' => 'Article déjà en ligne',
            'payment_refunded' => 'Article remboursé et dépublié',
            'rejected' => 'Workflow terminé',
            default => 'Vérifier la soumission',
        };
    @endphp

    @include('filament.pages.partials.editorial-page-styles')

    <div class="vp-wrap">
        <div class="vp-hero">
            <div class="vp-hero-circle" style="width:160px;height:160px;top:-32px;right:-32px"></div>
            <div class="vp-hero-inner">
                <div class="vp-hero-box">
                    <div class="vp-hero-box-step">Editorial</div>
                    <div class="vp-hero-box-title">Afficher soumission</div>
                </div>
                <div class="vp-hero-text" style="flex:1">
                    <p>Vue de décision simple : résumé, étape actuelle, paiement, puis contenu complet.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu'il se passe ici</h4>
                <p>Lis d’abord le résumé ci-dessous. Tu sauras immédiatement où en est la soumission et ce qu’il reste à faire avant même de lire tout l’article.</p>
            </div>
        </div>

        <section class="vp-card">
            <div class="vp-card-body">
                <h2 class="vp-title" style="font-size:28px">{{ $submission['title'] }}</h2>
                <div class="vp-meta">
                    <span>{{ $submission['author'] }}</span>
                    @if ($submission['author_email'])
                        <span>•</span>
                        <span>{{ $submission['author_email'] }}</span>
                    @endif
                    <span>•</span>
                    <span>{{ $submission['reading_time'] }} min</span>
                    <span>•</span>
                    <span>{{ $submission['created_at'] }}</span>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;margin-top:20px">
                    <div style="border:1px solid rgba(0,66,65,0.1);border-radius:20px;padding:18px;background:#f8fbfa">
                        <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:rgba(0,66,65,.55)">Statut de la soumission</div>
                        <div style="margin-top:8px;font-size:20px;font-weight:700;color:{{ $status['text'] }}">{{ $status['label'] }}</div>
                        <div style="margin-top:8px;font-size:13px;color:rgba(0,66,65,.72)">{{ $nextStep }}</div>
                    </div>
                    <div style="border:1px solid rgba(0,66,65,0.1);border-radius:20px;padding:18px;background:#f8fbfa">
                        <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:rgba(0,66,65,.55)">Paiement</div>
                        <div style="margin-top:8px;font-size:20px;font-weight:700;color:{{ $paymentStatus['text'] ?? '#004241' }}">
                            {{ $paymentStatus['label'] ?? 'Aucun paiement' }}
                        </div>
                        @if ($submission['payment_amount'])
                            <div style="margin-top:8px;font-size:13px;color:rgba(0,66,65,.72)">{{ $submission['payment_amount'] }}</div>
                        @endif
                        @if ($submission['payment_failure'])
                            <div style="margin-top:8px;font-size:13px;color:#991b1b">{{ $submission['payment_failure'] }}</div>
                        @endif
                        @if ($submission['refund_reason'])
                            <div style="margin-top:8px;font-size:13px;color:rgba(0,66,65,.72)">Remboursement : {{ $submission['refund_reason'] }}</div>
                        @endif
                    </div>
                    <div style="border:1px solid rgba(0,66,65,0.1);border-radius:20px;padding:18px;background:#f8fbfa">
                        <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:rgba(0,66,65,.55)">Proposition de prix</div>
                        <div style="margin-top:8px;font-size:20px;font-weight:700;color:#004241">{{ $submission['quote_amount'] ?? 'Aucune proposition' }}</div>
                        @if ($articleType)
                            <div style="margin-top:8px;font-size:13px;color:rgba(0,66,65,.72)">Type : {{ $articleType }}</div>
                        @endif
                        @if ($submission['quote_preset'])
                            <div style="margin-top:6px;font-size:13px;color:rgba(0,66,65,.72)">Tarif : {{ $submission['quote_preset'] }}</div>
                        @endif
                        @if ($submission['quote_expires_at'])
                            <div style="margin-top:6px;font-size:13px;color:rgba(0,66,65,.72)">Expire le {{ $submission['quote_expires_at'] }}</div>
                        @endif
                    </div>
                    <div style="border:1px solid rgba(0,66,65,0.1);border-radius:20px;padding:18px;background:#f8fbfa">
                        <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:rgba(0,66,65,.55)">Suivi éditorial</div>
                        <div style="margin-top:8px;font-size:20px;font-weight:700;color:#004241">{{ $submission['reviewer'] ?? 'Pas encore relue' }}</div>
                        @if ($submission['reviewed_at'])
                            <div style="margin-top:8px;font-size:13px;color:rgba(0,66,65,.72)">Le {{ $submission['reviewed_at'] }}</div>
                        @endif
                        @if ($submission['reviewer_notes'])
                            <div style="margin-top:8px;font-size:13px;color:rgba(0,66,65,.72)">{{ $submission['reviewer_notes'] }}</div>
                        @endif
                    </div>
                </div>

                @if ($submission['is_revision'] || $submission['depublication_requested'] || $quoteStatus)
                    <div class="vp-badges" style="margin-top:18px">
                        <span class="vp-badge" style="background:#EBF1EF;color:#004241">{{ $submission['category'] }}</span>
                        @if ($submission['is_revision'])
                            <span class="vp-badge" style="background:#dbeafe;color:#1d4ed8">Révision gratuite</span>
                        @endif
                        @if ($submission['depublication_requested'])
                            <span class="vp-badge" style="background:#FFF0B6;color:#6b5200">Dépublication demandée</span>
                        @endif
                        @if ($quoteStatus)
                            <span class="vp-badge" style="background:{{ $quoteStatus['bg'] }};color:{{ $quoteStatus['text'] }}">{{ $quoteStatus['label'] }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        @if (!empty($submission['status_logs']))
            <section class="vp-card">
                <div class="vp-card-body">
                    <h3 class="vp-title" style="margin-top:0">Historique des statuts</h3>
                    <div style="display:grid;gap:12px">
                        @foreach ($submission['status_logs'] as $log)
                            <div style="border:1px solid rgba(0,66,65,0.1);border-radius:20px;padding:16px;background:#f8fbfa">
                                <div style="font-size:14px;font-weight:700;color:#004241">
                                    {{ $log['from'] ? ucfirst(str_replace('_', ' ', $log['from'])) . ' → ' : '' }}{{ ucfirst(str_replace('_', ' ', $log['to'])) }}
                                </div>
                                <div style="margin-top:4px;font-size:12px;color:rgba(0,66,65,0.55)">
                                    {{ $log['at'] }} · {{ $log['by'] }}
                                </div>
                                @if ($log['reason'])
                                    <div style="margin-top:8px;font-size:13px;color:rgba(0,66,65,0.72)">{{ $log['reason'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ($submission['excerpt'] !== '')
            <section class="vp-card">
                <div class="vp-card-body">
                    <h3 class="vp-title" style="margin-top:0">Extrait</h3>
                    <div class="vp-text" style="font-size:14px">{!! nl2br(e($submission['excerpt'])) !!}</div>
                </div>
            </section>
        @endif

        <section class="vp-card">
            <div class="vp-card-body">
                <h3 class="vp-title" style="margin-top:0">Contenu</h3>
                <div class="vp-text" style="font-size:14px;line-height:1.8;">
                    @php
                        $content = $submission['content'];
                    @endphp
                    @if (preg_match('/<\s*(p|h1|h2|h3|h4|ul|ol|li|blockquote|img|figure|div|section)\b/i', $content))
                        {!! $content !!}
                    @else
                        {!! collect(preg_split("/\R{2,}/", trim($content)) ?: [])
                            ->map(fn (string $paragraph): string => '<p>' . nl2br(e(trim($paragraph))) . '</p>')
                            ->implode('') !!}
                    @endif
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
