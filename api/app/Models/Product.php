<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable =
    [
        'name',
        'quantity',
        'reorder_level',
        'location',
        'expiry_date',
        'description',
        'bar_code',
        'category',
        'supplier',
        'brand',
    ];
    // protected $appends = ['last_supply_date', 'last_supply_quantity', 'supply_number'];
    protected $casts = [
        'expiry_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function currentPrice(): HasOne
    {
        return $this->hasOne(Price::class)->latestOfMany();
    }
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }
    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class);
    }
    public function getLastSupplyDateAttribute()
    {
        return $this->supplies->isNotEmpty() ?
            now()->parse($this->supplies()->latest('created_at')->first()->supply_date)->format('d.M.Y') : '-';
    }
    public function latestSupply()
    {
        return $this->supplies->isNotEmpty() ? $this->supplies()->latest('created_at')->first() : collect([]);
    }
    public function getLastSupplyQuantityAttribute()
    {
        return  $this->supplies->isNotEmpty() ? $this->supplies()->latest('created_at')->first()->quantity : 0;
    }
    public function getSupplyNumberAttribute()
    {
        return $this->latestSupply()->supply_number ?? '-';
    }
}
