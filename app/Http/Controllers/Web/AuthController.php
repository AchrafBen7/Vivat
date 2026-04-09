<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private function honeypotTriggered(Request $request): bool
    {
        return trim((string) $request->input('company_website', '')) !== '';
    }

    private function strongPasswordRules(): array
    {
        return [
            'required',
            'confirmed',
            PasswordRule::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols(),
        ];
    }

    private function strongPasswordMessages(): array
    {
        return [
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 12 caractères.',
            'password.mixed' => 'Le mot de passe doit contenir une majuscule et une minuscule.',
            'password.letters' => 'Le mot de passe doit contenir des lettres.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le mot de passe doit contenir au moins un symbole.',
        ];
    }

    public function showBecomeContributor(Request $request): Response
    {
        $priceCents = (int) config('services.stripe.publication_price', 1500);
        $priceEur = (int) round($priceCents / 100);

        $content = render_php_view('site.become_contributor', [
            'publication_price_eur' => $priceEur,
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => content_locale($request),
            'title' => 'Devenir rédacteur Vivat',
            'meta_description' => 'Rédigez et publiez vos articles sur Vivat. Découvrez la participation à la publication et les avantages de devenir contributeur.',
            'hide_cta_section' => true,
            'hide_footer' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

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
            'title' => 'Créer votre compte Vivat',
            'meta_description' => 'Inscrivez-vous sur Vivat pour devenir rédacteur contributeur et publier vos articles.',
            'hide_cta_section' => true,
            'hide_footer' => true,
            'trim_main_bottom' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function register(Request $request): RedirectResponse|Response
    {
        if ($this->honeypotTriggered($request)) {
            return back()->withErrors([
                'email' => 'Impossible de créer le compte pour le moment. Réessayez.',
            ])->withInput($request->except('password', 'password_confirmation', 'company_website'));
        }

        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'        => $this->strongPasswordRules(),
            'terms_accepted'  => ['required', 'accepted'],
        ], array_merge([
            'first_name.required' => 'Le prénom est obligatoire.',
            'first_name.max' => 'Le prénom ne peut pas dépasser 255 caractères.',
            'last_name.required' => 'Le nom est obligatoire.',
            'last_name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required' => "L'email est obligatoire.",
            'email.email' => "L'email n'est pas valide.",
            'email.max' => "L'email ne peut pas dépasser 255 caractères.",
            'email.unique' => 'Cet email est déjà utilisé.',
            'terms_accepted.required' => 'Vous devez accepter les conditions d\'utilisation.',
            'terms_accepted.accepted' => 'Vous devez accepter les conditions d\'utilisation.',
        ], $this->strongPasswordMessages()));

        $name = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $user = User::create([
            'name'     => $name,
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'language' => 'fr',
        ]);

        $user->assignRole('contributor');

        Auth::login($user);
        $request->session()->regenerate();

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
            'title' => 'Connexion Vivat',
            'meta_description' => 'Connectez-vous à votre compte contributeur Vivat.',
            'hide_cta_section' => true,
            'hide_footer' => true,
            'trim_main_bottom' => true,
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
            'email.email' => "L'email n'est pas valide.",
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = $request->user();

            if ($user?->hasRole('admin')) {
                return redirect()->to('/admin')->with('success', 'Connexion réussie.');
            }

            if ($user?->hasRole('contributor')) {
                return redirect()->route('contributor.dashboard')->with('success', 'Connexion réussie.');
            }

            return redirect('/')->with('success', 'Connexion réussie.');
        }

        return back()->withErrors([
            'email' => 'Les identifiants sont incorrects.',
        ])->onlyInput('email');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        if (! $this->googleOAuthConfigured()) {
            return redirect()->route('login')->with('error', "La connexion Google n'est pas encore configurée.");
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if (! $this->googleOAuthConfigured()) {
            return redirect()->route('login')->with('error', "La connexion Google n'est pas encore configurée.");
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            return redirect()->route('login')->with('error', 'Impossible de finaliser la connexion Google.');
        }

        $email = mb_strtolower(trim((string) ($googleUser->getEmail() ?? '')));

        if ($email === '') {
            return redirect()->route('login')->with('error', "Votre compte Google ne fournit pas d'adresse email exploitable.");
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user === null) {
            $name = trim((string) ($googleUser->getName() ?: $googleUser->getNickname() ?: 'Utilisateur Google'));

            try {
                $user = User::create([
                    'name' => $name !== '' ? $name : 'Utilisateur Google',
                    'email' => $email,
                    'google_id' => $googleUser->getId(),
                    'password' => Str::password(32),
                    'language' => 'fr',
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } catch (QueryException $exception) {
                return redirect()->route('login')->with('error', 'Ce compte Google ne peut pas être relié pour le moment.');
            }

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            $user->assignRole('contributor');
        } else {
            $updates = [];

            if (($user->google_id === null || $user->google_id === '') && $googleUser->getId()) {
                $updates['google_id'] = $googleUser->getId();
            }

            if (empty($user->avatar) && $googleUser->getAvatar()) {
                $updates['avatar'] = $googleUser->getAvatar();
            }

            if ($user->email_verified_at === null) {
                $updates['email_verified_at'] = now();
            }

            if ($updates !== []) {
                $user->update($updates);
                $user = $user->fresh();
            }
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        if ($user->hasRole('admin')) {
            return redirect('/admin')->with('success', 'Connexion Google réussie.');
        }

        return redirect()->route('contributor.dashboard')->with('success', 'Connexion Google réussie.');
    }

    public function showForgotPasswordForm(Request $request): Response
    {
        $errors = $request->session()->get('errors');
        $old = $request->old();
        $content = render_php_view('site.forgot_password', [
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
            'status' => session('status'),
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => content_locale($request),
            'title' => 'Mot de passe oublié Vivat',
            'meta_description' => 'Recevez un lien de réinitialisation de mot de passe pour votre compte Vivat.',
            'hide_cta_section' => true,
            'hide_footer' => true,
            'trim_main_bottom' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        if ($this->honeypotTriggered($request)) {
            return back()->with('status', "Si un compte existe pour cette adresse, un lien de réinitialisation vient d'être envoyé.");
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => "L'email est obligatoire.",
            'email.email' => "L'email n'est pas valide.",
        ]);

        $status = PasswordBroker::sendResetLink([
            'email' => $validated['email'],
        ]);

        if ($status === PasswordBroker::RESET_LINK_SENT) {
            return back()->with('status', "Si un compte existe pour cette adresse, un lien de réinitialisation vient d'être envoyé.");
        }

        return back()->withErrors([
            'email' => "Impossible d'envoyer le lien de réinitialisation pour le moment.",
        ])->onlyInput('email');
    }

    public function showResetPasswordForm(Request $request, string $token): Response
    {
        $errors = $request->session()->get('errors');
        $old = $request->old();
        $content = render_php_view('site.reset_password', [
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
            'token' => $token,
            'email' => (string) $request->query('email', $old['email'] ?? ''),
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => content_locale($request),
            'title' => 'Réinitialiser le mot de passe Vivat',
            'meta_description' => 'Choisissez un nouveau mot de passe pour votre compte Vivat.',
            'hide_cta_section' => true,
            'hide_footer' => true,
            'trim_main_bottom' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => $this->strongPasswordRules(),
        ], array_merge([
            'token.required' => 'Le lien de réinitialisation est invalide.',
            'email.required' => "L'email est obligatoire.",
            'email.email' => "L'email n'est pas valide.",
        ], $this->strongPasswordMessages()));

        $status = PasswordBroker::reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $validated['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Votre mot de passe a été réinitialisé. Vous pouvez maintenant vous connecter.');
        }

        return back()->withErrors([
            'email' => 'Le lien de réinitialisation est invalide ou a expiré.',
        ])->withInput($request->except('password', 'password_confirmation'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Déconnexion réussie.');
    }

    private function googleOAuthConfigured(): bool
    {
        return (string) config('services.google.client_id', '') !== ''
            && (string) config('services.google.client_secret', '') !== ''
            && (string) config('services.google.redirect', '') !== '';
    }
}
