<?php

namespace App\Modules\HR\Positions\Http\Requests;

use App\Modules\HR\Positions\DTO\PositionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('position')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $positionId = $this->route('position')?->id;

        return [
            'departement_id' => ['required', 'integer', Rule::exists('hr_departements', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', Rule::unique('hr_positions', 'code')->ignore($positionId)],
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
