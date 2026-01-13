<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionDefault extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_name', 
        'default_quota'
    ];

    protected $casts = [
        'default_quota' => 'integer',
    ];
}