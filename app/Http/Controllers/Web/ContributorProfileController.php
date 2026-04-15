<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ContributorProfileController extends ContributorBaseController
{
    public function profile(Request $request): Response|RedirectResponse
    {
        if ($request->isMethod('post')) {
            if ($request->input('form_type') === 'delete_account') {
                $user = $request->user();

                if ($user->hasRole('admin')) {
                    return redirect()
                        ->back()
                        ->withErrors(['delete_account' => "La suppression automatique d'un compte administrateur est bloquée pour préserver l'accès au back-office."])
                        ->withInput();
                }

                $rules = [
                    'delete_email' => ['required', 'email'],
                    'delete_confirmation' => ['accepted'],
                ];

                if (blank($user->google_id)) {
                    $rules['current_password_delete'] = ['required', 'current_password'];
                }

                $validated = $request->validate($rules, [
                    'delete_email.required' => 'Veuillez confirmer votre adresse email.',
                    'delete_email.email' => 'Veuillez entrer une adresse email valide.',
                    'delete_confirmation.accepted' => 'Vous devez confirmer la suppression définitive du compte.',
                    'current_password_delete.required' => 'Votre mot de passe actuel est obligatoire pour supprimer le compte.',
                    'current_password_delete.current_password' => 'Le mot de passe actuel est incorrect.',
                ]);

                if (! hash_equals((string) $user->email, (string) $validated['delete_email'])) {
                    return redirect()
                        ->back()
                        ->withErrors(['delete_email' => "L'adresse email de confirmation ne correspond pas à votre compte."])
                        ->withInput();
                }

                $this->accountDeletionService->anonymize($user);

                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('home')
                    ->with('success', 'Votre compte a été supprimé et vos données personnelles ont été anonymisées.');
            }

            if ($request->input('form_type') === 'password') {
                $validated = $request->validate([
                    'current_password' => ['required', 'current_password'],
                    'password' => [
                        'required',
                        'confirmed',
                        Password::min(8)->mixedCase()->numbers()->symbols(),
                    ],
                ], [
                    'current_password.required' => 'Votre mot de passe actuel est obligatoire.',
                    'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
                    'password.required' => 'Le nouveau mot de passe est obligatoire.',
                    'password.confirmed' => 'Les nouveaux mots de passe ne correspondent pas.',
                    'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
                    'password.mixed' => 'Le nouveau mot de passe doit contenir une majuscule et une minuscule.',
                    'password.numbers' => 'Le nouveau mot de passe doit contenir au moins un chiffre.',
                    'password.symbols' => 'Le nouveau mot de passe doit contenir au moins un symbole.',
                ]);

                $request->user()->forceFill([
                    'password' => $validated['password'],
                ])->save();

                return redirect()->route('contributor.profile')->with('success', 'Mot de passe mis à jour.');
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'bio' => ['nullable', 'string', 'max:2000'],
                'instagram_url' => ['nullable', 'url', 'max:255'],
                'twitter_url' => ['nullable', 'url', 'max:255'],
                'website_url' => ['nullable', 'url', 'max:255'],
            ], [
                'name.required' => 'Le nom complet est obligatoire.',
                'name.max' => 'Le nom complet ne peut pas dépasser 255 caractères.',
                'bio.max' => 'La biographie ne peut pas dépasser 2000 caractères.',
                'instagram_url.url' => "Le lien Instagram doit etre une URL valide.",
                'instagram_url.max' => "Le lien Instagram est trop long.",
                'twitter_url.url' => "Le lien Twitter doit etre une URL valide.",
                'twitter_url.max' => "Le lien Twitter est trop long.",
                'website_url.url' => "Le site web doit etre une URL valide.",
                'website_url.max' => "Le site web est trop long.",
            ]);

            $request->user()->update([
                'name' => $validated['name'],
                'bio' => $validated['bio'] ?? null,
                'instagram_url' => $validated['instagram_url'] ?? null,
                'twitter_url' => $validated['twitter_url'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
            ]);

            return redirect()->route('contributor.profile')->with('success', 'Profil mis à jour.');
        }

        $errors = $request->session()->get('errors');
        $old = $request->old();

        return $this->renderContributorPage('profile', 'site.contributor.profile', [
            'user' => $request->user(),
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
        ]);
    }
}
