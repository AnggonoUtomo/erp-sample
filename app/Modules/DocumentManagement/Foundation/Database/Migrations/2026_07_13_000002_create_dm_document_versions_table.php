<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dm_document_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('dm_documents')->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('storage_object_key');
            $table->string('original_filename');
            $table->string('declared_media_type', 100);
            $table->string('detected_media_type', 100);
            $table->unsignedBigInteger('byte_size');
            $table->char('sha256', 64);
            $table->string('status', 20)->default('STAGED');
            $table->string('scan_status', 30)->default('NOT_CONFIGURED');
            $table->string('created_by_reference');
            $table->timestamps();
            $table->unique(['document_id', 'version_number']);
            $table->index(['status', 'created_at']);
        });

        Schema::table('dm_idempotency_keys', function (Blueprint $table): void {
            $table->foreignId('version_id')->nullable()->after('document_id')
                ->constrained('dm_document_versions')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dm_idempotency_keys', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('version_id');
        });
        Schema::dropIfExists('dm_document_versions');
    }
};
