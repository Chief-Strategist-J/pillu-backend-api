<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RequestList extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_email','user_id'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
