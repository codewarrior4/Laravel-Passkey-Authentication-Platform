<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('passkeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->string('status')->default('pending');
            $table->string('user_handle');
            $table->text('credential_id')->nullable();
            $table->string('credential_id_hash', 64)->nullable()->unique();
            $table->text('public_key')->nullable();
            $table->json('transports')->nullable();
            $table->unsignedBigInteger('counter')->default(0);
            $table->text('current_challenge')->nullable();
            $table->timestamp('challenge_expires_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['device_id', 'status']);
            $table->index(['user_id', 'revoked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passkeys');
    }
};
