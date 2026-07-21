<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('phone')->nullable()->unique()->after('email');
            });
        }

        if (Schema::hasColumn('carts', 'customer_id')) {
            Schema::table('carts', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('customer_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('carts', 'customer_id')) {
            Schema::table('carts', function (Blueprint $table): void {
                $table->foreignUlid('customer_id')->nullable()->after('session_id')->constrained()->nullOnDelete();
            });
        }

        if (Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique(['phone']);
                $table->dropColumn('phone');
            });
        }
    }
};
