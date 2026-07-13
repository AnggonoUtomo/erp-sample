<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dm_delivery_tokens', function (Blueprint $table): void {
            $table->id();
            $table->char('token_hash', 64)->unique();
            $table->foreignId('document_id')->constrained('dm_documents')->restrictOnDelete();
            $table->foreignId('version_id')->constrained('dm_document_versions')->restrictOnDelete();
            $table->char('owner_fingerprint', 64);
            $table->string('actor_reference');
            $table->string('action', 32);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_by_reference')->nullable();
            $table->timestamps();

            $table->index(['actor_reference', 'action', 'expires_at'], 'dm_delivery_actor_action_expiry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dm_delivery_tokens');
    }
};
