<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_media', function (Blueprint $table): void {
            $table->binary('content')->nullable()->after('path');
            $table->string('source_url')->nullable()->after('content');
            $table->string('checksum_sha256', 64)->nullable()->after('size_bytes');
            $table->index(['product_id', 'mime_type']);
        });
    }

    public function down(): void
    {
        Schema::table('product_media', function (Blueprint $table): void {
            $table->dropIndex(['product_id', 'mime_type']);
            $table->dropColumn(['content', 'source_url', 'checksum_sha256']);
        });
    }
};
