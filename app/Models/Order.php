<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id', 
        'customer_name', 
        'customer_email', 
        'customer_phone', 
        'fungsio_id',    
        'total_amount', // [CRITICAL] Tambahkan ini agar nominal total tersimpan
        'payment_proof', 
        'status',
        'is_received'
    ];

    protected $casts = [
        'is_received' => 'boolean',
        'total_amount' => 'integer',
    ];

    // Relasi ke Item (One to Many)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi ke Batch 
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    // Relasi ke Fungsio (Pengurus)
    public function fungsio()
    {
        return $this->belongsTo(Fungsio::class, 'fungsio_id');
    }
}