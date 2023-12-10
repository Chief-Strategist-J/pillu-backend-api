<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FireAuthUser;
use App\Models\OneSignalUserProfile;
use Illuminate\Support\Facades\DB;


class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userProfile = FireAuthUser::where('user_id', '=', $this->id)->get()->first();
        $oneSignalUserProfile = OneSignalUserProfile::where('user_id', '=', $this->id)->get()->first();


        // $userProfile = DB::table('fire_auth_users')->where('user_id', '=', $this->id)->first();
        // $oneSignalUserProfile = DB::table('one_signal_user_profiles')->where('user_id', '=', $this->id)->first();

        return [
            'id' => $this->id,
            'user_profile' => $userProfile,
            'onesignal_user_profile' => $oneSignalUserProfile,
        ];
    }
}
