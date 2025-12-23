<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // KITA IZINKAN KOLOM INI UNTUK DIISI:
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'order_details', // <--- Wajib ada!
        'payment_proof', // <--- Wajib ada!
        'status'
    ];
}