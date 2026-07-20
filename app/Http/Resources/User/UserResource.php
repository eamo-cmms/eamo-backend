<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roleValue = $this->role instanceof \BackedEnum ? $this->role->value : $this->role;
        $department = $this->relationLoaded('department') ? $this->department : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $roleValue,
            'roles' => $roleValue ? [$roleValue] : [],
            'department_id' => $this->department_id,
            'department_name' => $department?->name,
            'company_name' => $department?->company?->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
