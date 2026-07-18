<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->after('user_id')->constrained('hr_employees')->nullOnDelete();
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('place_of_birth', 120)->nullable()->after('date_of_birth');
            $table->string('national_id', 64)->nullable()->unique()->after('place_of_birth');
            $table->text('address')->nullable()->after('national_id');
            $table->string('emergency_contact_name', 160)->nullable()->after('address');
            $table->string('emergency_contact_phone', 40)->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relation', 80)->nullable()->after('emergency_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn([
                'supervisor_id', 'date_of_birth', 'place_of_birth', 'national_id', 'address',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
            ]);
        });
    }
};
