<?php

namespace App\Modules\HR\JobLevels\Http\Requests;

use App\Modules\HR\JobLevels\DTO\JobLevelData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('jobLevel')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $jobLevelId = $this->route('jobLevel')?->id;

        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', Rule::unique('hr_job_levels', 'code')->ignore($jobLevelId)],
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
