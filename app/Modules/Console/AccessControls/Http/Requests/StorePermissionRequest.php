<?php

namespace App\Modules\Console\AccessControls\Http\Requests;

use App\Modules\Console\AccessControls\DTO\PermissionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->where('guard_name', 'web')],
        ];
    }

    public function toDto(): PermissionData
    {
        return PermissionData::fromArray($this->validated());
    }
}
