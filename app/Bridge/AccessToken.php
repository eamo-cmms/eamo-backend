<?php

namespace App\Bridge;

use App\Models\User;
use DateTimeImmutable;
use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKeyInterface;

class AccessToken extends PassportAccessToken
{
    protected ?CryptKeyInterface $privateKeyInstance = null;

    public function setPrivateKey(CryptKeyInterface $privateKey): void
    {
        $this->privateKeyInstance = $privateKey;
        parent::setPrivateKey($privateKey);
    }

    /**
     * Generate a JWT from the access token.
     */
    public function convertToJWT(): Token
    {
        $privateKeyContents = $this->privateKeyInstance?->getKeyContents() ?? '';

        $jwtConfiguration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKeyContents, $this->privateKeyInstance?->getPassPhrase() ?? ''),
            InMemory::plainText('empty', 'empty')
        );

        $userId = $this->getUserIdentifier();
        $roles = [];

        if ($userId) {
            $user = User::whereKey($userId)->first();
            if ($user && $user->role) {
                $roleValue = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
                $roles = [strtolower((string) $roleValue)];
            }
        }

        $builder = $jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable)
            ->canOnlyBeUsedAfter(new DateTimeImmutable)
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo($userId ?? $this->getClient()->getIdentifier())
            ->withClaim('scopes', $this->getScopes());

        // Add user roles to JWT claims
        if (! empty($roles)) {
            $builder = $builder->withClaim('roles', $roles)
                ->withClaim('role', $roles[0]);
        }

        return $builder->getToken($jwtConfiguration->signer(), $jwtConfiguration->signingKey());
    }

    public function toString(): string
    {
        return $this->convertToJWT()->toString();
    }
}
