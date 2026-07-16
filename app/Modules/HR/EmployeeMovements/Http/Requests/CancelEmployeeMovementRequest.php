<?php

namespace App\Modules\HR\EmployeeMovements\Http\Requests;

use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use Illuminate\Foundation\Http\FormRequest;

class CancelEmployeeMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $movement = $this->route('employeeMovement');

        return $movement instanceof EmployeeMovement
            && ($this->user()?->can('cancel', $movement) ?? false);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}
