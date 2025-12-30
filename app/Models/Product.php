<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description', 
        'price', 
        'image',
        'is_active'
    ];


    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_product')
                    ->withPivot('price', 'stock', 'sold', 'is_active')
                    ->withTimestamps();
    }
}