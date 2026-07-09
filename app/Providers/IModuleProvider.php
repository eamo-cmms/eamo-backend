<?php

declare(strict_types=1);

namespace App\Providers;

interface IModuleProvider
{
    public function seed(): void;

    public function getRoutePath(): string;

    public function getMigrationPath(): string;

    public function registerPolicies(): void;
}
