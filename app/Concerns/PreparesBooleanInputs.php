<?php

declare(strict_types=1);

namespace App\Concerns;

trait PreparesBooleanInputs
{
    /**
     * Cast the given request inputs to boolean values.
     *
     * @param  array<int, string>  $fields
     */
    protected function prepareBooleans(array $fields): void
    {
        $merge = [];
        foreach ($fields as $field) {
            if ($this->has($field)) {
                $merge[$field] = $this->boolean($field);
            }
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }
}
