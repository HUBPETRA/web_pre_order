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
        'banner_image',
        'mail_message',      
        'close_date',
        'pickup_date',
        'fine_per_unit',
        'is_active',
        'is_reminder_sent'   
    ];

    /**
     * Casting tipe data otomatis.
     */
    protected $casts = [
        'close_date' => 'date',
        'pickup_date' => 'date',
        'is_active' => 'boolean',
        'is_reminder_sent' => 'boolean',
        'fine_per_unit' => 'integer',
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

    public function quotas()
    {
        return $this->hasMany(BatchQuota::class);
    }
}