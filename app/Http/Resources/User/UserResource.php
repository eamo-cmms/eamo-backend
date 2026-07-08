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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->role ? [$this->role] : [],
            'department_id' => $this->department_id,
            'department_name' => $this->relationLoaded('department') ? $this->department?->name : null,
            'company_name' => ($this->relationLoaded('department') && $this->department?->relationLoaded('company'))
                ? $this->department?->company?->name
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
