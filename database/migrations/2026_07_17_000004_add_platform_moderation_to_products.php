<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_blocked')->default(false)->after('is_new');
            $table->timestampTz('blocked_at')->nullable()->after('is_blocked');
            $table->foreignId('blocked_by')->nullable()->after('blocked_at')->constrained('users')->nullOnDelete();
            $table->text('blocked_reason')->nullable()->after('blocked_by');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('blocked_by');
            $table->dropColumn(['is_blocked', 'blocked_at', 'blocked_reason']);
        });
    }
};
