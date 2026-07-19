<?php

namespace App\Modules\Console\BackupRestores\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FullRestoreBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('backup-restore.full-restore') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'backup' => ['required', 'file', 'max:524288'],
            'restore_database' => ['nullable', 'boolean'],
            'restore_storage_public' => ['nullable', 'boolean'],
            'dry_run' => ['nullable', 'boolean'],
            'confirmation' => ['required', 'string', Rule::in(['RESTORE FULL BACKUP'])],
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

            if ($extension !== 'zip') {
                $validator->errors()->add('backup', 'Full restore hanya menerima signed full backup .zip.');
            }
        });
    }
}
