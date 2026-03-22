<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'language' => ['sometimes', 'in:fr,nl'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'], // hashed via cast
            'language' => $validated['language'] ?? 'fr',
        ]);

        $user->assignRole('contributor');

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'user'    => $this->userPayload($user),
            'token'   => $token,
        ], 201);
    }

    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // Revoke previous tokens (single-device strategy)
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'user'    => $this->userPayload($user),
            'token'   => $token,
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    /**
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'language'  => ['sometimes', 'in:fr,nl'],
            'interests' => ['sometimes', 'array'],
            'interests.*' => ['string', 'max:100'],
            'bio'       => ['sometimes', 'nullable', 'string', 'max:2000'],
            'instagram_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'twitter_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'website_url' => ['sometimes', 'nullable', 'url', 'max:255'],
        ]);

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour.',
            'user'    => $this->userPayload($request->user()->fresh()),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Private                                                           */
    /* ------------------------------------------------------------------ */

    private function userPayload(User $user): array
    {
        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'language'  => $user->language,
            'interests' => $user->interests,
            'avatar'    => $user->avatar,
            'bio'       => $user->bio,
            'instagram_url' => $user->instagram_url,
            'twitter_url' => $user->twitter_url,
            'website_url' => $user->website_url,
            'roles'     => $user->getRoleNames(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
