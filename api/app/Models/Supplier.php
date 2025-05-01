<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'email', 'phone', 'address'];
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function supplies()
    {
        return $this->hasManyThrough(Supply::class, Product::class);
    }
}
