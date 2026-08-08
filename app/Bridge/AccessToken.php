<?php

namespace App\Bridge;

use App\Models\User;
use DateTimeImmutable;
use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;
use Lcobucci\JWT\Token;

class AccessToken extends PassportAccessToken
{
    /**
     * Generate a JWT from the access token.
     *
     * @return Token
     */
    public function convertToJWT()
    {
        $this->initBuilder();

        $userId = $this->getUserIdentifier();
        $roles = [];

        if ($userId) {
            $user = User::find($userId);
            if ($user && $user->role) {
                $roleValue = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
                $roles = [strtolower((string) $roleValue)];
            }
        }

        $builder = $this->builder
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable)
            ->canOnlyBeUsedAfter(new DateTimeImmutable)
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string) $userId)
            ->withClaim('scopes', $this->getScopes());

        // Add user roles to JWT claims
        if (! empty($roles)) {
            $builder = $builder->withClaim('roles', $roles)
                ->withClaim('role', $roles[0]);
        }

        return $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }
}
