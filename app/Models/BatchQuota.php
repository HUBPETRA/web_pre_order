<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchQuota extends Model
{
    protected $fillable = ['batch_id', 'fungsio_id', 'target_qty', 'last_reminded_at'];

    // Relasi ke Fungsio (supaya tahu ini target punya siapa)
    public function fungsio()
    {
        return $this->belongsTo(Fungsio::class);
    }
}