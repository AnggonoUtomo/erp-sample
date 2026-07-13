<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dm_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedSmallInteger('owner_schema_version');
            $table->string('owner_domain', 64);
            $table->string('owner_aggregate_type', 128);
            $table->string('owner_aggregate_id');
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->string('created_by_reference');
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['owner_domain', 'owner_aggregate_type', 'owner_aggregate_id'],
                'dm_documents_owner_context_index',
            );
            $table->index(['status', 'created_at']);
        });

        Schema::create('dm_idempotency_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('scope', 100);
            $table->char('key_hash', 64);
            $table->char('request_fingerprint', 64);
            $table->foreignId('document_id')->constrained('dm_documents')->restrictOnDelete();
            $table->string('status', 20)->default('COMPLETED');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['scope', 'key_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dm_idempotency_keys');
        Schema::dropIfExists('dm_documents');
    }
};
