<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountDeletionService
{
    public function anonymize(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $originalEmail = (string) $user->email;

            NewsletterSubscriber::query()
                ->where('email', $originalEmail)
                ->delete();

            DB::table('password_reset_tokens')
                ->where('email', $originalEmail)
                ->delete();

            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();

            DB::table('reading_histories')
                ->where('user_id', $user->id)
                ->delete();

            $user->tokens()->delete();
            $user->syncPermissions([]);
            $user->syncRoles([]);

            $deletedEmail = Str::lower('deleted+' . str_replace('-', '', $user->id) . '@example.invalid');

            $user->forceFill([
                'name' => 'Compte supprimé',
                'email' => Str::limit($deletedEmail, 255, ''),
                'google_id' => null,
                'password' => Str::password(40),
                'language' => 'fr',
                'interests' => null,
                'avatar' => null,
                'bio' => null,
                'instagram_url' => null,
                'twitter_url' => null,
                'website_url' => null,
                'email_verified_at' => null,
                'remember_token' => Str::random(60),
                'account_deleted_at' => now(),
            ])->save();
        });
    }
}
