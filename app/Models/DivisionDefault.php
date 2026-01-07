<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisionDefault extends Model
{
    protected $fillable = ['division_name', 'default_quota'];
}