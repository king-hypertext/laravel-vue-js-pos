<?php

namespace App\Models;

use App\Observers\SaleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(SaleObserver::class)]
class Sale extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'customer_id',
        'number',
        'sale_date',
        'total_amount',
        'payment_method',
        'sold_by'
    ];
    protected $withCount = ['saleItems'];
    protected $casts = [
        'created_at' => 'datetime',
        'sale_date' => 'datetime'
    ];
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, SaleItem::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }
}
