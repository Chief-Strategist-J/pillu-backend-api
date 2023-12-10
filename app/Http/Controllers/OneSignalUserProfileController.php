<?php

namespace App\Http\Controllers;

use App\Models\OneSignalUserProfile;
use Illuminate\Http\Request;

class OneSignalUserProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function sendNotification(Request $req)
    {
        $message = $req->input('message');
        $subscription_id = $req->input('subscription_id');

        $curl = curl_init();

        $payload = [
            "app_id" => "018fc56c-431f-492b-9142-756c7e246e75",
            "include_player_ids" => [
                $subscription_id
            ],
            "data" => [
                "key" => ""
            ],
            "contents" => [
                "en" => $message
            ]
        ];

        $jsonData = json_encode($payload);

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://onesignal.com/api/v1/notifications',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json; charset=utf-8',
                    'Authorization: Basic MjY1YzcyODEtZmY1Zi00NjgxLTk3NjAtMDViMzBiNWEwYWMy'
                ),
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);

        return response()->json(
            [
                'status' => true,
                'error' => 'N/A',
                'message' => 'OneSignal Subscription id is successfully updated',
                'data' => json_decode($response, true),
            ],
            200
        );
    }

   
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $req)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OneSignalUserProfile $oneSignalUserProfile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OneSignalUserProfile $oneSignalUserProfile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OneSignalUserProfile $oneSignalUserProfile)
    {
        //
    }
}
