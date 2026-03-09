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
    public function showRegisterForm(): Response
    {
        $content = render_php_view('site.register', [
            'errors' => [],
            'old' => [],
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'title' => 'Créer un compte — Vivat',
            'meta_description' => 'Inscrivez-vous sur Vivat pour devenir rédacteur et publier vos articles.',
            'header_variant' => 'auth',
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function register(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => "L'email est obligatoire.",
            'email.email' => "L'email n'est pas valide.",
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'language' => 'fr',
        ]);

        $user->assignRole('contributor');

        Auth::login($user);

        return redirect('/')->with('success', 'Compte créé avec succès. Bienvenue sur Vivat !');
    }

    public function showLoginForm(): Response
    {
        $content = render_php_view('site.login', [
            'errors' => [],
            'old' => [],
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'title' => 'Connexion — Vivat',
            'meta_description' => 'Connectez-vous à votre compte Vivat.',
            'header_variant' => 'auth',
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
