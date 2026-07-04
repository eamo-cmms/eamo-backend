<?php

namespace App\Providers;

use App\Bridge\AccessTokenRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Passport::useClientModel(\App\Models\OAuth\Client::class);

        Passport::authorizationView(function () {
            return response('Authorization view not configured. Please use first-party trusted clients.', 403);
        });

        Passport::tokensExpireIn(now()->addDays(1));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Bind custom AccessTokenRepository to include roles in JWT
        $this->app->singleton(
            AccessTokenRepositoryInterface::class,
            function ($app) {
                return new AccessTokenRepository(
                    $app->make(TokenRepository::class),
                    $app->make(Dispatcher::class)
                );
            }
        );
    }
}
