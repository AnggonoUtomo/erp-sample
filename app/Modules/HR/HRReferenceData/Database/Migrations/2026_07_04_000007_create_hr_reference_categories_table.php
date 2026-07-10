<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_reference_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'sort_order']);
            $table->index('deleted_at');
        });

        if (Schema::hasTable('hr_reference_data')) {
            DB::table('hr_reference_data')
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->each(function (string $category, int $index) {
                    DB::table('hr_reference_categories')->updateOrInsert(
                        ['code' => $category],
                        [
                            'name' => str($category)->replace('-', ' ')->title()->toString(),
                            'active' => true,
                            'sort_order' => ($index + 1) * 10,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_reference_categories');
    }
};
