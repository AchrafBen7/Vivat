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
                        ->withErrors(['delete_account' => __('site.validation_delete_admin_blocked')])
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
                    'delete_email.required' => __('site.validation_delete_email_required'),
                    'delete_email.email' => __('site.validation_delete_email_invalid'),
                    'delete_confirmation.accepted' => __('site.validation_delete_confirmation_required'),
                    'current_password_delete.required' => __('site.validation_current_password_delete_required'),
                    'current_password_delete.current_password' => __('site.validation_current_password_invalid'),
                ]);

                if (! hash_equals((string) $user->email, (string) $validated['delete_email'])) {
                    return redirect()
                        ->back()
                        ->withErrors(['delete_email' => __('site.validation_delete_email_mismatch')])
                        ->withInput();
                }

                $this->accountDeletionService->anonymize($user);

                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('home')
                    ->with('success', __('site.flash_account_deleted'));
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
                    'current_password.required' => __('site.validation_current_password_delete_required'),
                    'current_password.current_password' => __('site.validation_current_password_invalid'),
                    'password.required' => __('site.validation_new_password_required'),
                    'password.confirmed' => __('site.validation_new_password_confirmed'),
                    'password.min' => __('site.validation_new_password_min'),
                    'password.mixed' => __('site.validation_new_password_mixed'),
                    'password.numbers' => __('site.validation_new_password_numbers'),
                    'password.symbols' => __('site.validation_new_password_symbols'),
                ]);

                $request->user()->forceFill([
                    'password' => $validated['password'],
                ])->save();

                return redirect()->route('contributor.profile')->with('success', __('site.flash_password_updated'));
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'bio' => ['nullable', 'string', 'max:2000'],
                'instagram_url' => ['nullable', 'url', 'max:255'],
                'twitter_url' => ['nullable', 'url', 'max:255'],
                'website_url' => ['nullable', 'url', 'max:255'],
            ], [
                'name.required' => __('site.validation_name_required'),
                'name.max' => __('site.validation_name_max'),
                'bio.max' => __('site.validation_bio_max'),
                'instagram_url.url' => __('site.validation_instagram_invalid'),
                'instagram_url.max' => __('site.validation_instagram_max'),
                'twitter_url.url' => __('site.validation_twitter_invalid'),
                'twitter_url.max' => __('site.validation_twitter_max'),
                'website_url.url' => __('site.validation_website_invalid'),
                'website_url.max' => __('site.validation_website_max'),
            ]);

            $request->user()->update([
                'name' => $validated['name'],
                'bio' => $validated['bio'] ?? null,
                'instagram_url' => $validated['instagram_url'] ?? null,
                'twitter_url' => $validated['twitter_url'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
            ]);

            return redirect()->route('contributor.profile')->with('success', __('site.flash_profile_updated'));
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
