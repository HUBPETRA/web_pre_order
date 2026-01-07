<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'batch_id', 
        'customer_name', 
        'customer_email', // <-- BARU
        'customer_phone', 
        'fungsio_id',     // <-- BARU
        'payment_proof', 
        'status'
    ];

    // Relasi ke Item One to Many
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi ke Batch 
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    public function fungsio()
    {
        return $this->belongsTo(Fungsio::class, 'fungsio_id');
    }
}