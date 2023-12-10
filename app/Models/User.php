<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\FireAuthUser;
use App\Models\OneSignalUserProfile;
use App\Models\UserFriendList;
use App\Models\InvitationList;
use App\Models\RequestList;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'name',
        'created_at', 'updated_at', 'email_verified_at'
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function fireAuthUser()
    {
        return $this->hasOne(FireAuthUser::class);
    }

    public function oneSignalProfile()
    {
        return $this->hasOne(OneSignalUserProfile::class);
    }

    public function friendLists()
    {
        return $this->hasMany(UserFriendList::class);
    }

    public function invitationLists()
    {
        return $this->hasMany(InvitationList::class);
    }

    public function requestList()
    {
        return $this->hasMany(RequestList::class);
    }
}
