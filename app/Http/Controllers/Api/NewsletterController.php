<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function __construct(
        private readonly NewsletterSubscriptionService $newsletterSubscriptionService,
    ) {}

    /**
     * POST /api/newsletter/subscribe
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'     => ['required', 'email', 'max:255'],
            'name'      => ['nullable', 'string', 'max:255'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string', 'max:100'],
        ]);

        $result = $this->newsletterSubscriptionService->subscribe($validated);

        return response()->json([
            'message' => $result['message'],
        ], $result['status'] === 'created' ? 201 : 200);
    }

    /**
     * POST /api/newsletter/unsubscribe
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $result = $this->newsletterSubscriptionService->unsubscribe($validated['token']);

        return response()->json(
            ['message' => $result['message']],
            $result['status'] === 'unsubscribed' ? 200 : 404
        );
    }

    /**
     * GET /api/newsletter/confirm?token=xxx
     */
    public function confirm(Request $request): JsonResponse
    {
        $result = $this->newsletterSubscriptionService->confirm($request->input('token'));

        return response()->json(
            ['message' => $result['message']],
            $result['status'] === 'confirmed' ? 200 : ($result['status'] === 'missing_token' ? 422 : 404)
        );
    }

    /**
     * GET /api/admin/newsletter/subscribers admin listing (within admin group)
     */
    public function subscribers(Request $request): JsonResponse
    {
        $query = NewsletterSubscriber::query();

        if ($request->filled('active_only')) {
            $query->active();
        }

        $subscribers = $query->orderByDesc('created_at')->paginate(30);

        return response()->json([
            'data' => $subscribers->map(fn ($s) => [
                'id'              => $s->id,
                'email'           => $s->email,
                'name'            => $s->name,
                'interests'       => $s->interests,
                'confirmed'       => $s->confirmed,
                'confirmed_at'    => $s->confirmed_at?->toIso8601String(),
                'unsubscribed_at' => $s->unsubscribed_at?->toIso8601String(),
                'created_at'      => $s->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'total'        => $subscribers->total(),
                'per_page'     => $subscribers->perPage(),
                'current_page' => $subscribers->currentPage(),
            ],
        ]);
    }
}
