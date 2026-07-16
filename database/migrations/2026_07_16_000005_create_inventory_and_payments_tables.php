<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->uuid('checkout_token')->nullable()->unique()->after('number');
        });

        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('on_hand')->default(0);
            $table->unsignedInteger('reserved')->default(0);
            $table->unsignedInteger('reorder_level')->default(5);
            $table->timestampsTz();
        });

        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignUlid('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->integer('quantity_delta');
            $table->unsignedInteger('on_hand_after');
            $table->unsignedInteger('reserved_after');
            $table->timestampTz('created_at');
            $table->index(['inventory_item_id', 'created_at']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('stripe');
            $table->string('provider_payment_id')->nullable()->unique();
            $table->uuid('idempotency_key')->unique();
            $table->string('status')->index();
            $table->char('currency', 3);
            $table->unsignedBigInteger('amount_minor');
            $table->text('client_secret')->nullable();
            $table->json('provider_payload')->default('{}');
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('processed_webhooks', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('provider');
            $table->string('provider_event_id');
            $table->string('event_type');
            $table->timestampTz('processed_at');
            $table->unique(['provider', 'provider_event_id']);
        });

        Schema::create('outbox_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('aggregate_type');
            $table->string('aggregate_id');
            $table->json('payload');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestampTz('available_at');
            $table->timestampTz('processed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestampsTz();
            $table->index(['processed_at', 'available_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE inventory_items ADD CONSTRAINT inventory_reservation_valid CHECK (reserved <= on_hand)');
            DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_valid CHECK (status IN ('creating', 'requires_confirmation', 'processing', 'succeeded', 'failed', 'cancelled', 'refunded'))");
            DB::statement('CREATE INDEX outbox_pending_idx ON outbox_messages (available_at) WHERE processed_at IS NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_messages');
        Schema::dropIfExists('processed_webhooks');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_items');
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('checkout_token');
        });
    }
};
