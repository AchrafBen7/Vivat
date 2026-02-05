<?php

namespace App\Policies;

use App\Models\RssFeed;
use App\Models\User;

class RssFeedPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, RssFeed $rssFeed): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, RssFeed $rssFeed): bool
    {
        return true;
    }

    public function delete(?User $user, RssFeed $rssFeed): bool
    {
        return true;
    }
}
