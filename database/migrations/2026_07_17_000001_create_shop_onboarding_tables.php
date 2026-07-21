<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_onboardings', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('shop_name');
            $table->string('shop_slug')->unique();
            $table->string('status')->index();
            $table->text('last_error')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('shops', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignId('owner_id')->unique()->constrained('users')->restrictOnDelete();
            $table->foreignUlid('onboarding_id')->unique()->constrained('shop_onboardings')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->index();
            $table->char('currency', 3)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->timestampsTz();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignUlid('shop_id')->nullable()->after('id')->constrained('shops')->nullOnDelete();
            $table->index(['shop_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('shop_id');
        });
        Schema::dropIfExists('shops');
        Schema::dropIfExists('shop_onboardings');
    }
};
