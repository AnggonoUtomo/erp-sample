<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->replaceModelType('model_has_roles', 'App\\Modules\\Users\\Models\\User', User::class);
        $this->replaceModelType('model_has_permissions', 'App\\Modules\\Users\\Models\\User', User::class);
        $this->replaceModelType('media', 'App\\Modules\\Users\\Models\\User', User::class);
    }

    public function down(): void
    {
        $this->replaceModelType('model_has_roles', User::class, 'App\\Modules\\Users\\Models\\User');
        $this->replaceModelType('model_has_permissions', User::class, 'App\\Modules\\Users\\Models\\User');
        $this->replaceModelType('media', User::class, 'App\\Modules\\Users\\Models\\User');
    }

    private function replaceModelType(string $table, string $from, string $to): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'model_type')) {
            return;
        }

        DB::table($table)
            ->where('model_type', $from)
            ->update(['model_type' => $to]);
    }
};
