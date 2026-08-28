<?php

declare(strict_types=1);

namespace App\Providers;

use App\Bridge\AccessToken;
use App\Bridge\AccessTokenRepository;
use App\Enums\UserRole;
use App\Models\OAuth\Client;
use App\Models\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Spatie\LaravelPackageTools\Modules\ModuleRegistry;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            AccessTokenRepositoryInterface::class,
            fn ($app) => new AccessTokenRepository($app->make(Dispatcher::class))
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $this->configureAuthorization();
        $this->configurePassport();
        $this->discoverModules();
    }

    /**
     * Register host-app modules with the package's ModuleRegistry so their
     * routes are auto-loaded alongside the package's own modules.
     */
    private function discoverModules(): void
    {
        /** @var ModuleRegistry $registry */
        $registry = $this->app->make(ModuleRegistry::class);
        $registry->discover(base_path('modules'));
    }

    /**
     * Configure automatic policy discovery and super-admin gate authorization.
     */
    private function configureAuthorization(): void
    {
        // Automatically resolve policies across Core App and all Modules:
        // [Namespace]\Models\[Model] -> [Namespace]\Policies\[Model]Policy
        Gate::guessPolicyNamesUsing(
            fn (string $modelClass): string => str_replace('\\Models\\', '\\Policies\\', $modelClass).'Policy'
        );

        // Super-admin bypass: Admin gets unrestricted access to all abilities
        Gate::before(
            fn (User $user, string $ability): ?bool => $user->hasRole(UserRole::Admin) ? true : null
        );
    }

    /**
     * Configure Laravel Passport OAuth2 settings and token lifetimes.
     */
    private function configurePassport(): void
    {
        Passport::useClientModel(Client::class);
        Passport::useAccessTokenEntity(AccessToken::class);

        Passport::authorizationView(
            fn () => response('Authorization view not configured. Please use first-party trusted clients.', 403)
        );

        Passport::tokensExpireIn(now()->addMinutes(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
