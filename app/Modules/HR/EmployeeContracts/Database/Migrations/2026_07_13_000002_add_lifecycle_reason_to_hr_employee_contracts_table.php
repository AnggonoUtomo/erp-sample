<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_contracts', fn (Blueprint $table) => $table->string('ended_reason', 500)->nullable()->after('status'));
    }

    public function down(): void
    {
        Schema::table('hr_employee_contracts', fn (Blueprint $table) => $table->dropColumn('ended_reason'));
    }
};
