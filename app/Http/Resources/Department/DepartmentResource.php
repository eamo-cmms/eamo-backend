<?php

namespace App\Http\Resources\Department;

use App\Http\Resources\Company\CompanyResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Department
 */
class DepartmentResource extends JsonResource
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
            'company_id' => $this->company_id,
            'name' => $this->name,
            'contact' => $this->contact,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'company_name' => $this->relationLoaded('company') ? $this->company?->name : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
