<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment;

use Spatie\LaravelPackageTools\Modules\AbstractModuleProvider;

final class Register extends AbstractModuleProvider
{
    public function getDomain(): string
    {
        return 'Masterdata';
    }

    public function getName(): string
    {
        return 'Equipment';
    }
}
