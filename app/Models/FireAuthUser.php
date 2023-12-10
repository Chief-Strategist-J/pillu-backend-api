<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FireAuthUser extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'photoUrl', 'email', 'userName', 'firebase_user_id', 'password', 'firstname', 'lastname','user_id'
    ];

    protected $hidden = [
        'password', 'created_at', 'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
