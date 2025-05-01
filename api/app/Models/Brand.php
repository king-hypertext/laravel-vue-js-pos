<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'supllier_id'
    ];
    public function supllier(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
