<?php

namespace App\Modules\HR\JobLevels\Http\Requests;

use App\Modules\HR\JobLevels\DTO\JobLevelData;
use App\Modules\HR\JobLevels\Models\JobLevel;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', JobLevel::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', 'unique:hr_job_levels,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function toDto(): JobLevelData
    {
        return JobLevelData::fromArray($this->validated());
    }
}
