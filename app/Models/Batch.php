<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'bank_name', 
        'bank_account_number', 
        'bank_account_name', 
        'whatsapp_link', 
        'is_active'
    ];

    // Relasi ke Produk (Many-to-Many)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'batch_product')
                    ->withPivot('price', 'stock', 'sold', 'is_active')
                    ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}