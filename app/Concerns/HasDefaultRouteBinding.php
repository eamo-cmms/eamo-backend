<?php

declare(strict_types=1);

namespace App\Concerns;

trait HasDefaultRouteBinding
{
    public function getRouteKeyName(): string
    {
        return $this->getKeyName();
    }
}
