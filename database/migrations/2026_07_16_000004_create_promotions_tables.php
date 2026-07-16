<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('type');
            $table->unsignedBigInteger('value');
            $table->unsignedBigInteger('minimum_subtotal_minor')->default(0);
            $table->unsignedBigInteger('maximum_discount_minor')->nullable();
            $table->unsignedInteger('priority')->default(100);
            $table->string('stacking_mode')->default('best_price');
            $table->boolean('is_active')->default(true);
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at')->nullable();
            $table->timestampsTz();
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('product_promotion', function (Blueprint $table): void {
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('promotion_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'promotion_id']);
        });

        Schema::create('category_promotion', function (Blueprint $table): void {
            $table->foreignUlid('category_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('promotion_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'promotion_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE promotions ADD CONSTRAINT promotions_type_valid CHECK (type IN ('percentage', 'fixed_amount'))");
            DB::statement("ALTER TABLE promotions ADD CONSTRAINT promotions_stacking_valid CHECK (stacking_mode IN ('exclusive', 'stackable', 'best_price'))");
            DB::statement('ALTER TABLE promotions ADD CONSTRAINT promotions_window_valid CHECK (ends_at IS NULL OR ends_at > starts_at)');
            DB::statement("ALTER TABLE promotions ADD CONSTRAINT promotions_percentage_valid CHECK (type <> 'percentage' OR value BETWEEN 1 AND 100)");
            DB::statement('CREATE INDEX promotions_current_idx ON promotions (priority, starts_at) WHERE is_active = true');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_promotion');
        Schema::dropIfExists('product_promotion');
        Schema::dropIfExists('promotions');
    }
};
