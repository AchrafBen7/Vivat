<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    /**
     * POST /api/public/preferences
     * Stocker les centres d'intérêt pour un visiteur (cookie-based via session_id)
     * ou pour un utilisateur connecté.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required_without:user_id', 'nullable', 'string', 'max:100'],
            'interests'  => ['required', 'array', 'min:1'],
            'interests.*' => ['string', 'max:100'],
            'language'   => ['sometimes', 'in:fr,nl'],
        ]);

        $userId = $request->user()?->id;
        $sessionId = $validated['session_id'] ?? null;

        // If user is authenticated, update their profile directly
        if ($userId) {
            $request->user()->update([
                'interests' => $validated['interests'],
                'language'  => $validated['language'] ?? $request->user()->language,
            ]);

            return response()->json([
                'message'   => 'Préférences sauvegardées sur votre profil.',
                'interests' => $validated['interests'],
            ]);
        }

        // Otherwise, store in user_preferences table (cookie-based)
        $preference = UserPreference::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'interests' => $validated['interests'],
                'language'  => $validated['language'] ?? 'fr',
            ]
        );

        return response()->json([
            'message'   => 'Préférences sauvegardées.',
            'interests' => $preference->interests,
        ]);
    }

    /**
     * GET /api/public/preferences?session_id=xxx
     * Récupérer les préférences d'un visiteur.
     */
    public function show(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $sessionId = $request->input('session_id');

        if ($userId) {
            return response()->json([
                'interests' => $request->user()->interests ?? [],
                'language'  => $request->user()->language,
            ]);
        }

        if ($sessionId) {
            $pref = UserPreference::where('session_id', $sessionId)->first();
            if ($pref) {
                return response()->json([
                    'interests' => $pref->interests ?? [],
                    'language'  => $pref->language,
                ]);
            }
        }

        return response()->json([
            'interests' => [],
            'language'  => 'fr',
        ]);
    }
}
