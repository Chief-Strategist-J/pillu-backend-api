<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppConfiguration extends Model
{
    use HasFactory;
    protected $fillable = ['platform_name', 'key', 'value'];
    protected $hidden = ['created_at', 'updated_at'];
}
