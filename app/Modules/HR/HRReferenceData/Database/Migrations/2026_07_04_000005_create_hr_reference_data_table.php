<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_reference_data', function (Blueprint $table) {
            $table->id();
            $table->string('category', 64);
            $table->string('code', 64);
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['category', 'code'], 'hr_reference_data_category_code_unique');
            $table->index(['category', 'active', 'sort_order'], 'hr_reference_data_category_active_sort_index');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_reference_data');
    }
};
