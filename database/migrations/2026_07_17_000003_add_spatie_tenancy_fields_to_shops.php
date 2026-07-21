<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            $table->string('domain')->nullable()->unique()->after('slug');
            $table->string('database')->nullable()->unique()->after('domain');
            $table->boolean('is_accessible')->default(true)->after('status');
            $table->timestampTz('blocked_at')->nullable()->after('is_accessible');
            $table->text('blocked_reason')->nullable()->after('blocked_at');
        });

        DB::table('shops')->orderBy('created_at')->each(function (object $shop): void {
            DB::table('shops')
                ->where('id', $shop->id)
                ->update([
                    'domain' => $shop->slug.'.'.parse_url(config('app.url'), PHP_URL_HOST),
                    'database' => 'tenant_'.str_replace('-', '_', $shop->slug),
                    'is_accessible' => true,
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            $table->dropColumn(['domain', 'database', 'is_accessible', 'blocked_at', 'blocked_reason']);
        });
    }
};
