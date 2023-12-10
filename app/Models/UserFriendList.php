<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserFriendList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'is_accepted',
        'is_blocked'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'is_accepted', 'is_blocked', 'id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
