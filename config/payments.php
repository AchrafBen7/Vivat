<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Refund Policy
    |--------------------------------------------------------------------------
    |
    | Defines what happens when a paid submission is refunded after publication.
    | depublish: move the article back to draft when the refund is confirmed.
    | keep_published: keep the article live and log the refund for audit.
    |
    */
    'published_refund_policy' => env('PAYMENTS_PUBLISHED_REFUND_POLICY', 'depublish'),
];
