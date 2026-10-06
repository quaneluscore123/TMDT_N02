<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'transaction_code',
        'amount',
        'status',
        'paid_at',
        'response_data',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'response_data' => 'array',
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
