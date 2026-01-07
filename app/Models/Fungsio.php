<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fungsio extends Model
{
    use HasFactory;
    
    // Tambahkan 'division' ke dalam array
    protected $fillable = ['name', 'email', 'division', 'is_active'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}