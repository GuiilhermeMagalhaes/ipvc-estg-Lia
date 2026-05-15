<?php

namespace App\Policies;

use App\User;
use App\Grupo;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function gerir(User $user)
        {  
            
         return Grupo::findOrFail($user->grupo_id)->gerirUsers;   
        }
}
