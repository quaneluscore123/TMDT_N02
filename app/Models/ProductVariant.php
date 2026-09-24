<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'size', 'color', 'stock', 'price',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'price' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function label(): string
    {
        return collect([$this->size, $this->color])->filter()->implode(' - ');
    }

    public function unitPrice(): int
    {
        return $this->price ?? $this->product?->effectivePrice() ?? 0;
    }
}
