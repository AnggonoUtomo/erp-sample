<?php

namespace App\Modules\HR\Onboardings\Http\Requests;

use App\Modules\HR\Onboardings\DTO\OnboardingTaskAssignmentData;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignOnboardingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'assignee_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn (Builder $query) => $query->whereNull('deleted_at'))],
        ];
    }

    public function toDto(): OnboardingTaskAssignmentData
    {
        $assignee = $this->validated('assignee_user_id');

        return new OnboardingTaskAssignmentData($assignee === null ? null : (int) $assignee);
    }
}
