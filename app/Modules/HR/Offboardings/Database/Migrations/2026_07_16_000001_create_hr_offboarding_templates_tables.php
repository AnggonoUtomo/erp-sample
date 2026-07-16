<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_offboarding_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr_offboarding_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offboarding_template_id')
                ->constrained('hr_offboarding_templates')
                ->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 64);
            $table->boolean('required')->default(true);
            $table->smallInteger('due_offset_days')->default(0);
            $table->string('default_assignee_role', 100)->nullable();
            $table->unsignedInteger('sort_order');
            $table->timestamps();

            $table->unique(
                ['offboarding_template_id', 'sort_order'],
                'hr_offboarding_template_items_order_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_offboarding_template_items');
        Schema::dropIfExists('hr_offboarding_templates');
    }
};
