<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance;

use Spatie\LaravelPackageTools\Modules\AbstractModuleProvider;

final class Register extends AbstractModuleProvider
{
    public function getDomain(): string
    {
        return 'Equipment';
    }

    public function getName(): string
    {
        return 'Maintenance';
    }
}
