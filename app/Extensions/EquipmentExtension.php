<?php

declare(strict_types=1);

namespace App\Extensions;

use Spatie\LaravelPackageTools\Contracts\TableExtension;
use Spatie\LaravelPackageTools\Extensions\ColumnDefinition;

class EquipmentExtension implements TableExtension
{
    /**
     * Get the target table name to extend.
     */
    public function targetTable(): string
    {
        return 'eamo_equipment';
    }

    /**
     * Get the column definitions for the target table.
     *
     * @return array<int, ColumnDefinition>
     */
    public function columns(): array
    {
        return [
            ColumnDefinition::make('parent_id', 'string')
                ->length(36)
                ->nullable()
                ->after('id'),
        ];
    }

    /**
     * Get the priority order of the extension.
     */
    public function priority(): int
    {
        return 10;
    }
}
