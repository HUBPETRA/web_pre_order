<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id', 
        'fungsio_id', 
        'target_qty', 
        'last_reminded_at',
        'is_fine_paid'
    ];

    protected $casts = [
        'target_qty' => 'integer',
        'is_fine_paid' => 'boolean',
        'last_reminded_at' => 'date',
    ];

    // Relasi ke Fungsio (Milik siapa target ini?)
    public function fungsio()
    {
        return $this->belongsTo(Fungsio::class);
    }

    // Relasi ke Batch (Target untuk batch mana?)
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}