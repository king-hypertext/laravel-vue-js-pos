<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    
}
