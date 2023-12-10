<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class RequestListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        DB::table('request_lists')->where('user_id', '=', $this->id)->get();

        return [
            'email' => $this->email,
            'request_list' => DB::table('request_lists')->where('user_id', '=', $this->id)->get('requested_email'),
        ];
    }
}
