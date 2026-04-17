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
            PasswordRule::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols(),
        ];
    }

    private function strongPasswordMessages(): array
    {
        return [
            'password.required' => __('site.validation_password_required'),
            'password.confirmed' => __('site.validation_password_confirmed'),
            'password.min' => __('site.validation_password_min'),
            'password.mixed' => __('site.validation_password_mixed'),
            'password.letters' => __('site.validation_password_letters'),
            'password.numbers' => __('site.validation_password_numbers'),
            'password.symbols' => __('site.validation_password_symbols'),
        ];
    }

    public function showBecomeContributor(Request $request): Response
    {
        $locale = content_locale($request);
        $priceCents = (int) config('services.stripe.publication_price', 1500);
        $priceEur = (int) round($priceCents / 100);

        $content = render_php_view('site.become_contributor', [
            'publication_price_eur' => $priceEur,
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => 'Devenir rédacteur Vivat',
            'meta_description' => 'Rédigez et publiez vos articles sur Vivat. Découvrez la participation à la publication et les avantages de devenir contributeur.',
            'hide_cta_section' => true,
            'hide_footer' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function showRegisterForm(Request $request): Response
    {
        $locale = content_locale($request);
        $errors = $request->session()->get('errors');
        $old = $request->old();
        $content = render_php_view('site.register', [
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
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
                'email' => __('site.validation_auth_retry'),
            ])->withInput($request->except('password', 'password_confirmation', 'company_website'));
        }

        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'        => $this->strongPasswordRules(),
            'terms_accepted'  => ['required', 'accepted'],
        ], array_merge([
            'first_name.required' => __('site.validation_first_name_required'),
            'first_name.max' => __('site.validation_first_name_max'),
            'last_name.required' => __('site.validation_last_name_required'),
            'last_name.max' => __('site.validation_last_name_max'),
            'email.required' => __('site.validation_email_required'),
            'email.email' => __('site.validation_email_invalid'),
            'email.max' => __('site.validation_email_max'),
            'email.unique' => __('site.validation_email_unique'),
            'terms_accepted.required' => __('site.validation_terms_required'),
            'terms_accepted.accepted' => __('site.validation_terms_required'),
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

        return redirect()->route('contributor.dashboard')->with('success', __('site.flash_account_create_success'));
    }

    public function showLoginForm(Request $request): Response
    {
        $locale = content_locale($request);
        $errors = $request->session()->get('errors');
        $old = $request->old();
        $content = render_php_view('site.login', [
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
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
            'email.required' => __('site.validation_email_required'),
            'email.email' => __('site.validation_email_invalid'),
            'password.required' => __('site.validation_password_required'),
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = $request->user();

            if ($user?->hasRole('admin')) {
                return redirect()->to('/admin')->with('success', __('site.flash_login_success'));
            }

            if ($user?->hasRole('contributor')) {
                return redirect()->route('contributor.dashboard')->with('success', __('site.flash_login_success'));
            }

            return redirect('/')->with('success', __('site.flash_login_success'));
        }

        return back()->withErrors([
            'email' => __('site.validation_login_invalid'),
        ])->onlyInput('email');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        if (! $this->googleOAuthConfigured()) {
            return redirect()->route('login')->with('error', __('site.flash_google_not_configured'));
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if (! $this->googleOAuthConfigured()) {
            return redirect()->route('login')->with('error', __('site.flash_google_not_configured'));
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            return redirect()->route('login')->with('error', __('site.flash_google_login_failed'));
        }

        $email = mb_strtolower(trim((string) ($googleUser->getEmail() ?? '')));

        if ($email === '') {
            return redirect()->route('login')->with('error', __('site.flash_google_email_missing'));
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
                return redirect()->route('login')->with('error', __('site.flash_google_link_failed'));
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
            return redirect('/admin')->with('success', __('site.flash_google_login_success'));
        }

        return redirect()->route('contributor.dashboard')->with('success', __('site.flash_google_login_success'));
    }

    public function showForgotPasswordForm(Request $request): Response
    {
        $locale = content_locale($request);
        $errors = $request->session()->get('errors');
        $old = $request->old();
        $content = render_php_view('site.forgot_password', [
            'errors' => $errors ? $errors->getBag('default')->getMessages() : [],
            'old' => $old,
            'status' => session('status'),
        ]);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
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
            return back()->with('status', __('site.flash_reset_link_sent'));
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => __('site.validation_email_required'),
            'email.email' => __('site.validation_email_invalid'),
        ]);

        $status = PasswordBroker::sendResetLink([
            'email' => $validated['email'],
        ]);

        if ($status === PasswordBroker::RESET_LINK_SENT) {
            return back()->with('status', __('site.flash_reset_link_sent'));
        }

        return back()->withErrors([
            'email' => __('site.validation_reset_link_failed'),
        ])->onlyInput('email');
    }

    public function showResetPasswordForm(Request $request, string $token): Response
    {
        $locale = content_locale($request);
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
            'content_locale' => $locale,
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
            'token.required' => __('site.validation_reset_token_invalid'),
            'email.required' => __('site.validation_email_required'),
            'email.email' => __('site.validation_email_invalid'),
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
            return redirect()->route('login')->with('success', __('site.flash_password_reset_success'));
        }

        return back()->withErrors([
            'email' => __('site.validation_reset_token_expired'),
        ])->withInput($request->except('password', 'password_confirmation'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', __('site.flash_logout_success'));
    }

    private function googleOAuthConfigured(): bool
    {
        return (string) config('services.google.client_id', '') !== ''
            && (string) config('services.google.client_secret', '') !== ''
            && (string) config('services.google.redirect', '') !== '';
    }
}
