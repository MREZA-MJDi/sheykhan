<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProtectedFileView extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'product_file_id',
        'entitlement_id',
        'page_number',
        'event_type',
        'ip_address',
        'user_agent',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'viewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function productFile(): BelongsTo
    {
        return $this->belongsTo(ProductFile::class);
    }

    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(ProductEntitlement::class);
    }
}
