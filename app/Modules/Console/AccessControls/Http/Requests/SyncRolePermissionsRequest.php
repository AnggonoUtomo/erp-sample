<?php

namespace App\Modules\Console\AccessControls\Http\Requests;

use App\Modules\Console\AccessControls\DTO\SyncRolePermissionsData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class SyncRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Role|null $role */
        $role = $this->route('role');

        return [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                'distinct',
                Rule::exists('permissions', 'name')->where('guard_name', $role?->guard_name ?? 'web'),
            ],
        ];
    }

    public function toDto(): SyncRolePermissionsData
    {
        return SyncRolePermissionsData::fromArray($this->validated());
    }
}
