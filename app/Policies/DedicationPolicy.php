<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Dedication;

class DedicationPolicy
{
    /**
     * Anyone can view public; owner can view always; friends: implement later.
     */
    public function view(?User $user, Dedication $dedication): bool
    {
        if ($dedication->visibility === 'public') {
            return true;
        }
        if ($user && $user->id === $dedication->user_id) {
            return true;
        }
        // TODO: implement 'friends' visibility check
        return $dedication->visibility !== 'private';
    }
}
