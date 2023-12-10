<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;



class OneSignalUserProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'onesignal_subscription_id',
        'onesignal_email',
        'onesignal_user_token',
        'onesignal_external_id',
    ];

    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
