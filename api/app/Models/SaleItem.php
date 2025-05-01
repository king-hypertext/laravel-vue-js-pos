<?php

namespace App\Models;

use App\Observers\SaleItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(SaleItemObserver::class)]
class SaleItem extends Model
{
    /** @use HasFactory<\Database\Factories\SaleItemFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable =
    ['sale_id', 'product_id', 'quantity', 'price', 'date'];
    protected $casts = ['date' => 'datetime', 'created_at' => 'datetime'];
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class, 'product_id');
    }
}
