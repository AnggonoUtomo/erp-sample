<?php

namespace App\Modules\HR\HRReports\Http\Requests;

use App\Modules\HR\HRReports\DTO\ExpiryReportFilters;
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
            'contract_within_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'document_within_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
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

    public function toContractExpiryFilters(): ExpiryReportFilters
    {
        $withinDays = $this->validated('contract_within_days');

        return new ExpiryReportFilters(
            asOf: $this->validated('as_of') ?: now()->toDateString(),
            withinDays: $withinDays === null ? 30 : (int) $withinDays,
        );
    }

    public function toDocumentExpiryFilters(): ExpiryReportFilters
    {
        $withinDays = $this->validated('document_within_days');

        return new ExpiryReportFilters(
            asOf: $this->validated('as_of') ?: now()->toDateString(),
            withinDays: $withinDays === null ? 30 : (int) $withinDays,
        );
    }
}
