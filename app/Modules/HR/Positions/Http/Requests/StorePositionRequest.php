<?php

namespace App\Modules\HR\Positions\Http\Requests;

use App\Modules\HR\Positions\DTO\PositionData;
use App\Modules\HR\Positions\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Position::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'departement_id' => ['required', 'integer', Rule::exists('hr_departements', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', 'unique:hr_positions,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function toDto(): PositionData
    {
        return PositionData::fromArray($this->validated());
    }
}
