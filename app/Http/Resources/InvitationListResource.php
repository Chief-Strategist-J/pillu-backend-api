<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class InvitationListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        DB::table('invitation_lists')->where('user_id', '=', $this->id)->get();

        return [
            'email' => $this->email,
            'invitation_lists' => DB::table('invitation_lists')->where('user_id', '=', $this->id)->get(),
        ];
    }
}
