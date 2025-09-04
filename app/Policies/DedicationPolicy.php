<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Dedication;

class DedicationPolicy
{
    public function viewAny(User $actor): bool { 
        return $actor->hasAnyRole(['admin','moderator']); 
    }

    public function view(User $actor, Dedication $d): bool { 
        return $actor->hasAnyRole(['admin','moderator']); 
    }

    public function approve(User $actor, Dedication $d): bool { 
        return $actor->hasAnyRole(['admin','moderator']); 
    }

    public function reject(User $actor, Dedication $d): bool { 
        return $actor->hasAnyRole(['admin','moderator']); 
    }
    
}
