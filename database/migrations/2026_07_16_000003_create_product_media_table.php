<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_media', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('position')->default(0);
            $table->string('alt_text')->nullable();
            $table->timestampsTz();
            $table->unique(['disk', 'path']);
            $table->index(['product_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_media');
    }
};
