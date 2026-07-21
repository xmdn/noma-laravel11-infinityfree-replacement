<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignUlid('shop_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignUlid('shop_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        $firstShopId = DB::table('shops')->orderBy('created_at')->value('id');

        if ($firstShopId !== null) {
            DB::table('categories')->whereNull('shop_id')->update(['shop_id' => $firstShopId]);
            DB::table('products')->whereNull('shop_id')->update(['shop_id' => $firstShopId]);
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique('categories_slug_unique');
            $table->unique(['shop_id', 'slug']);
            $table->index(['shop_id', 'is_active', 'sort_order']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique('products_slug_unique');
            $table->unique(['shop_id', 'slug']);
            $table->index(['shop_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['shop_id', 'slug']);
            $table->dropIndex(['shop_id', 'status', 'published_at']);
            $table->unique('slug');
            $table->dropConstrainedForeignId('shop_id');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique(['shop_id', 'slug']);
            $table->dropIndex(['shop_id', 'is_active', 'sort_order']);
            $table->unique('slug');
            $table->dropConstrainedForeignId('shop_id');
        });
    }
};
