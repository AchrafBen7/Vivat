<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * POST /api/newsletter/subscribe
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'     => ['required', 'email', 'max:255'],
            'name'      => ['nullable', 'string', 'max:255'],
            'interests' => ['required', 'array', 'min:3'],
            'interests.*' => ['string', 'max:100'],
        ]);

        $existing = NewsletterSubscriber::where('email', $validated['email'])->first();

        if ($existing) {
            if ($existing->unsubscribed_at) {
                // Re-subscribe
                $existing->update([
                    'interests'       => $validated['interests'],
                    'name'            => $validated['name'] ?? $existing->name,
                    'unsubscribed_at' => null,
                    'confirmed'       => false,
                    'confirm_token'   => \Illuminate\Support\Str::random(64),
                ]);

                // TODO: send confirmation email
                return response()->json([
                    'message' => 'Réinscription enregistrée. Un email de confirmation va vous être envoyé.',
                ]);
            }

            // Update interests
            $existing->update([
                'interests' => $validated['interests'],
                'name'      => $validated['name'] ?? $existing->name,
            ]);

            return response()->json([
                'message' => 'Préférences newsletter mises à jour.',
            ]);
        }

        $subscriber = NewsletterSubscriber::create([
            'email'     => $validated['email'],
            'name'      => $validated['name'] ?? null,
            'interests' => $validated['interests'],
        ]);

        // TODO: send confirmation email with $subscriber->confirm_token

        return response()->json([
            'message' => 'Inscription enregistrée. Un email de confirmation va vous être envoyé.',
        ], 201);
    }

    /**
     * POST /api/newsletter/unsubscribe
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $validated['token'])->first();

        if (! $subscriber) {
            return response()->json(['message' => 'Token invalide.'], 404);
        }

        $subscriber->unsubscribe();

        return response()->json(['message' => 'Désabonnement effectué.']);
    }

    /**
     * GET /api/newsletter/confirm?token=xxx
     */
    public function confirm(Request $request): JsonResponse
    {
        $token = $request->input('token');

        if (! $token) {
            return response()->json(['message' => 'Token requis.'], 422);
        }

        $subscriber = NewsletterSubscriber::where('confirm_token', $token)->first();

        if (! $subscriber) {
            return response()->json(['message' => 'Token invalide ou déjà utilisé.'], 404);
        }

        $subscriber->confirm();

        return response()->json(['message' => 'Abonnement confirmé. Bienvenue !']);
    }

    /**
     * GET /api/admin/newsletter/subscribers — admin listing (within admin group)
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
