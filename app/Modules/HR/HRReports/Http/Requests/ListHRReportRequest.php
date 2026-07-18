<?php

namespace App\Modules\HR\HRReports\Http\Requests;

use App\Modules\HR\HRReports\DTO\HeadcountReportFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListHRReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAny(['hr-reports.view', 'hr-reports.manage']) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'as_of' => ['nullable', 'date_format:Y-m-d'],
            'employment_status_id' => [
                'nullable',
                'integer',
                Rule::exists('hr_employment_statuses', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    public function toHeadcountFilters(): HeadcountReportFilters
    {
        $employmentStatusId = $this->integer('employment_status_id') ?: null;

        return new HeadcountReportFilters(
            asOf: $this->validated('as_of') ?: now()->toDateString(),
            employmentStatusIds: $employmentStatusId === null ? [] : [$employmentStatusId],
        );
    }
}
