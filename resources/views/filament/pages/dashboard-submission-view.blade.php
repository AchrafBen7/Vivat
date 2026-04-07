<x-filament-panels::page>
    @php
        $submission = $this->getSubmissionData();
        $status = match($submission['status']) {
            'approved' => ['label' => 'Approuvée', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            'rejected' => ['label' => 'Rejetée', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'draft' => ['label' => 'Brouillon', 'bg' => '#EBF1EF', 'text' => '#004241'],
            default => ['label' => 'En attente', 'bg' => '#fffbeb', 'text' => '#92400e'],
        };
        $paymentStatus = match($submission['payment_status']) {
            'paid' => ['label' => 'Payé', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            'refunded' => ['label' => 'Remboursé', 'bg' => '#f3f4f6', 'text' => '#4b5563'],
            'failed' => ['label' => 'Échoué', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'pending' => ['label' => 'En attente', 'bg' => '#fffbeb', 'text' => '#92400e'],
            default => null,
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
                    <p>Consulte la soumission complète, son état éditorial, son paiement et les notes de relecture au même endroit.</p>
                </div>
            </div>
        </div>

        <div class="vp-tip">
            <div class="vp-tip-icon">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </div>
            <div>
                <h4>Ce qu’il se passe ici</h4>
                <p>Cette fiche te permet de relire le contenu, voir le contexte du rédacteur et prendre une décision éditoriale sans passer par la vue standard Filament.</p>
            </div>
        </div>

        <section class="vp-card">
            @if (!empty($submission['cover']))
                <img src="{{ $submission['cover'] }}" alt="" class="vp-cover">
            @endif
            <div class="vp-card-body">
                <div class="vp-badges">
                    <span class="vp-badge" style="background:{{ $status['bg'] }};color:{{ $status['text'] }}">{{ $status['label'] }}</span>
                    <span class="vp-badge" style="background:#EBF1EF;color:#004241">{{ $submission['category'] }}</span>
                    @if ($submission['is_revision'])
                        <span class="vp-badge" style="background:#dbeafe;color:#1d4ed8">Révision gratuite</span>
                    @endif
                    @if ($submission['depublication_requested'])
                        <span class="vp-badge" style="background:#FFF0B6;color:#6b5200">Dépublication demandée</span>
                    @endif
                    @if ($paymentStatus)
                        <span class="vp-badge" style="background:{{ $paymentStatus['bg'] }};color:{{ $paymentStatus['text'] }}">{{ $paymentStatus['label'] }}</span>
                    @endif
                </div>

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

                @if ($submission['payment_amount'])
                    <div class="vp-author">
                        <strong>Paiement</strong> · {{ $submission['payment_amount'] }}
                        @if ($submission['refund_reason'])
                            <div style="margin-top:4px;color:rgba(0,66,65,0.55)">Remboursement : {{ $submission['refund_reason'] }}</div>
                        @endif
                    </div>
                @endif

                @if ($submission['reviewer'] || $submission['reviewed_at'] || $submission['reviewer_notes'])
                    <div class="vp-author">
                        <strong>Relecture</strong>
                        @if ($submission['reviewer'])
                            <div style="margin-top:4px">Par {{ $submission['reviewer'] }}</div>
                        @endif
                        @if ($submission['reviewed_at'])
                            <div style="margin-top:4px;color:rgba(0,66,65,0.55)">Le {{ $submission['reviewed_at'] }}</div>
                        @endif
                        @if ($submission['reviewer_notes'])
                            <div style="margin-top:8px;color:rgba(0,66,65,0.7)">{{ $submission['reviewer_notes'] }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </section>

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
                <div class="vp-text" style="font-size:14px; line-height:1.8;">
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
