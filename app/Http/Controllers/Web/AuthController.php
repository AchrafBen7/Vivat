<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegisterForm(Request $request): Response
    {
        $errors = $request->session()->get('errors');
        $old = $request->old();
        $content = render_php_view('site.register', [
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => content_locale($request),
            'title' => 'Créer votre compte — Vivat',
            'meta_description' => 'Inscrivez-vous sur Vivat pour devenir rédacteur contributeur et publier vos articles.',
            'hide_cta_section' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function register(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'        => ['required', 'confirmed', Password::defaults()],
            'terms_accepted'  => ['required', 'accepted'],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => "L'email est obligatoire.",
            'email.email' => "L'email n'est pas valide.",
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'terms_accepted.required' => 'Vous devez accepter les conditions d\'utilisation.',
            'terms_accepted.accepted' => 'Vous devez accepter les conditions d\'utilisation.',
        ]);

        $name = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $user = User::create([
            'name'     => $name,
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'language' => 'fr',
        ]);

        $user->assignRole('contributor');

        Auth::login($user);

        return redirect()->route('contributor.dashboard')->with('success', 'Compte créé avec succès ! Vous êtes maintenant rédacteur contributeur sur Vivat.');
    }

    public function showLoginForm(Request $request): Response
    {
        $errors = $request->session()->get('errors');
        $old = $request->old();
        $content = render_php_view('site.login', [
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => content_locale($request),
            'title' => 'Connexion — Vivat',
            'meta_description' => 'Connectez-vous à votre compte contributeur Vivat.',
            'hide_cta_section' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => "L'email est obligatoire.",
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/')->with('success', 'Connexion réussie.');
        }

        return back()->withErrors([
            'email' => 'Les identifiants sont incorrects.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Déconnexion réussie.');
    }
}
