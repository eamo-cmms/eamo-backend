<?php

namespace App\Models\OAuth;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @param  Authenticatable  $user
     * @param  array  $scopes
     * @return bool
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        // Auto-approve all OAuth clients (skip the authorize consent prompt)
        return true;
    }
}
