<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMedia extends Model
{
    use HasUlids;

    protected $table = 'product_media';

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'disk',
        'path',
        'content',
        'source_url',
        'mime_type',
        'size_bytes',
        'checksum_sha256',
        'position',
        'alt_text',
    ];

    /** @return BelongsTo<CatalogProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class, 'product_id');
    }
}
