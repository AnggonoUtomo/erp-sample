<?php

namespace App\Modules\Console\BackupRestores\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RestoreBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('backup-restore.restore') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'backup' => ['required', 'file', 'max:10240'],
            'restore_system_settings' => ['nullable', 'boolean'],
            'restore_notification_templates' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $file = $this->file('backup');

            if (! $file) {
                return;
            }

            $extension = strtolower($file->getClientOriginalExtension());

            if (! in_array($extension, ['json', 'txt'], true)) {
                $validator->errors()->add('backup', 'File backup setting harus berformat .json atau .txt.');
            }
        });
    }
}
