<?php

namespace App\Jobs;

use App\Services\PublicationQuoteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExpirePublicationQuotesJob implements ShouldQueue
{
    use Queueable;

    public function handle(PublicationQuoteService $quoteService): void
    {
        $count = $quoteService->expireOverdueQuotes();

        if ($count > 0) {
            Log::info("ExpirePublicationQuotesJob: {$count} quote(s) expirée(s).");
        }
    }
}
