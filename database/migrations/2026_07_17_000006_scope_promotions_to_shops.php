<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table): void {
            $table->foreignUlid('shop_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        $firstShopId = DB::table('shops')->orderBy('created_at')->value('id');

        if ($firstShopId !== null) {
            DB::table('promotions')->whereNull('shop_id')->update(['shop_id' => $firstShopId]);
        }

        Schema::table('promotions', function (Blueprint $table): void {
            $table->dropUnique('promotions_code_unique');
            $table->unique(['shop_id', 'code']);
            $table->index(['shop_id', 'is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table): void {
            $table->dropUnique(['shop_id', 'code']);
            $table->dropIndex(['shop_id', 'is_active', 'starts_at', 'ends_at']);
            $table->unique('code');
            $table->dropConstrainedForeignId('shop_id');
        });
    }
};
