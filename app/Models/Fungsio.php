<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fungsio extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 
        'email', 
        'division', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}