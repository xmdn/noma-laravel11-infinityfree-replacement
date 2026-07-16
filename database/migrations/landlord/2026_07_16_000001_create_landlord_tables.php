<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'landlord';

    public function up(): void
    {
        Schema::connection('landlord')->create('tenants', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('pending')->index();
            $table->string('database_driver')->default('pgsql');
            $table->string('database_host');
            $table->unsignedSmallInteger('database_port')->default(5432);
            $table->string('database_name')->unique();
            $table->string('database_username');
            $table->text('database_password');
            $table->string('database_sslmode')->default('require');
            $table->timestampTz('provisioned_at')->nullable();
            $table->timestampTz('suspended_at')->nullable();
            $table->timestampsTz();
        });

        Schema::connection('landlord')->create('tenant_domains', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('hostname')->unique();
            $table->boolean('is_primary')->default(false);
            $table->timestampTz('verified_at')->nullable();
            $table->timestampsTz();
            $table->index(['tenant_id', 'is_primary']);
        });

        Schema::connection('landlord')->create('provisioning_operations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->string('status')->default('pending')->index();
            $table->string('current_step')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
        });

        Schema::connection('landlord')->create('provisioning_steps', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignUlid('provisioning_operation_id')->constrained('provisioning_operations')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('diagnostic')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->unique(['provisioning_operation_id', 'name']);
        });

        Schema::connection('landlord')->create('platform_admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestampsTz();
        });

        if (DB::connection('landlord')->getDriverName() === 'pgsql') {
            DB::connection('landlord')->statement("ALTER TABLE tenants ADD CONSTRAINT tenants_status_valid CHECK (status IN ('pending', 'provisioning', 'active', 'suspended', 'failed'))");
            DB::connection('landlord')->statement("ALTER TABLE tenants ADD CONSTRAINT tenants_driver_valid CHECK (database_driver = 'pgsql')");
            DB::connection('landlord')->statement('CREATE UNIQUE INDEX tenant_one_primary_domain_idx ON tenant_domains (tenant_id) WHERE is_primary = true');
            DB::connection('landlord')->statement("ALTER TABLE provisioning_operations ADD CONSTRAINT provisioning_status_valid CHECK (status IN ('pending', 'running', 'completed', 'failed'))");
            DB::connection('landlord')->statement("ALTER TABLE provisioning_steps ADD CONSTRAINT provisioning_step_status_valid CHECK (status IN ('pending', 'running', 'completed', 'failed', 'skipped'))");
        }
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('platform_admins');
        Schema::connection('landlord')->dropIfExists('provisioning_steps');
        Schema::connection('landlord')->dropIfExists('provisioning_operations');
        Schema::connection('landlord')->dropIfExists('tenant_domains');
        Schema::connection('landlord')->dropIfExists('tenants');
    }
};
