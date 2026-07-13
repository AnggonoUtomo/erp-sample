<?php

namespace App\Modules\Console\AuditLogs\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuditLogService
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function record(
        string $module,
        string $event,
        ?Model $auditable = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null,
        bool $fallbackToAuthenticatedActor = true,
    ): void {
        try {
            $request = app()->runningInConsole() ? null : request();
            if ($actor === null && $fallbackToAuthenticatedActor) {
                $actor = Auth::user();
            }

            AuditLog::query()->create([
                'actor_id' => $actor?->id,
                'module' => $module,
                'event' => $event,
                'auditable_type' => $auditable?->getMorphClass(),
                'auditable_id' => $auditable?->getKey(),
                'description' => $description,
                'old_values' => $oldValues ? $this->sanitize($oldValues) : null,
                'new_values' => $newValues ? $this->sanitize($newValues) : null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function sanitize(array $values): array
    {
        return Arr::except($values, [
            'password',
            'password_confirmation',
            'current_password',
            'remember_token',
            'smtp_password',
        ]);
    }
}
