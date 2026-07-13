<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_contracts', fn (Blueprint $table) => $table->foreignId('superseded_by_id')->nullable()->after('ended_reason')->constrained('hr_employee_contracts')->nullOnDelete());
    }

    public function down(): void
    {
        Schema::table('hr_employee_contracts', function (Blueprint $table) {
            $table->dropForeign(['superseded_by_id']);
            $table->dropColumn('superseded_by_id');
        });
    }
};
