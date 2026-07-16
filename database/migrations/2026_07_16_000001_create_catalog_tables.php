<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestampsTz();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->unsignedBigInteger('price_minor');
            $table->char('currency', 3)->default('USD');
            $table->text('primary_image_url');
            $table->json('colors')->default('[]');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->string('status')->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->timestampsTz();

            $table->index(['status', 'published_at']);
        });

        Schema::create('category_product', function (Blueprint $table): void {
            $table->foreignUlid('category_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'product_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_price_nonnegative CHECK (price_minor >= 0)');
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_currency_uppercase CHECK (currency = upper(currency))');
            DB::statement("CREATE INDEX products_active_catalog_idx ON products (published_at DESC, name) WHERE status = 'active' AND published_at IS NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
