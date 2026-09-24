<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'price', // Giá tại thời điểm thêm vào giỏ (snapshot)
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Subtotal của 1 dòng giỏ hàng (VNĐ).
     */
    public function subtotal(): int
    {
        $unit = $this->variant?->unitPrice() ?? $this->product->effectivePrice();

        return $this->quantity * $unit;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
