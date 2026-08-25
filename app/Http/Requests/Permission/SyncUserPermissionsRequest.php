<?php

declare(strict_types=1);

namespace App\Http\Requests\Permission;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SyncUserPermissionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only Admin can manage permissions
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'permissions' => ['present', 'array'],
            'permissions.*' => ['required', 'string', 'exists:permissions,id'],
        ];
    }

    public function targetUser(): ?User
    {
        $user = $this->route('user');

        return $user instanceof User ? $user : null;
    }

    /**
     * Custom validation to enforce role boundaries.
     *
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $targetUser = $this->targetUser();

                if (! $targetUser) {
                    return;
                }

                // If target user is an Engineer, verify no organization group permissions are provided
                if ($targetUser->hasRole(UserRole::Engineer)) {
                    $submittedPermissions = (array) $this->input('permissions', []);
                    $forbiddenPermissions = Permission::whereIn('id', $submittedPermissions)
                        ->where('group_name', 'organization')
                        ->pluck('id')
                        ->toArray();

                    if (! empty($forbiddenPermissions)) {
                        $validator->errors()->add(
                            'permissions',
                            'Engineer role cannot be granted organization management permissions: '.implode(', ', $forbiddenPermissions)
                        );
                    }
                }
            },
        ];
    }
}
