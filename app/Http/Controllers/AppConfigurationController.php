<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppConfigurationRequest;
use App\Models\AppConfiguration;
use Illuminate\Http\Request;

class AppConfigurationController extends Controller
{

    public function index()
    {
        try {
            return response()->json(
                [
                    'status' => true,
                    'error' => 'N/A',
                    'message' => 'App configuration retrieved successfully',
                    'data' => AppConfiguration::all(),
                ],
                200
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'status' => true,
                    'error' => $e,
                    'error_message' => $e->getMessage(),
                    'message' => 'Something went wrong',
                    'data' => [],
                ],
                500
            );
        }
    }


    public function create()
    {
        //
    }


    //StoreAppConfigurationRequest
    public function store(Request $request)
    {
        try {
            $appConfiguration =  AppConfiguration::create([
                'platform_name' => $request->input('platform_name'),
                'key' => $request->input('key'),
                'value' => $request->input('value'),
            ]);


            return response()->json(
                [
                    'status' => true,
                    'error' => 'N/A',
                    'message' => 'New Configuration Has Been Incorporated',
                    'data' => $appConfiguration,
                ],
                200
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'status' => true,
                    'error' => $e,
                    'error_message' => $e->getMessage(),
                    'message' => 'Something went wrong',
                    'data' => [],
                ],
                500
            );
        }
    }

    public function show(AppConfiguration $appConfiguration)
    {
        //
    }


    public function edit(Request $req)
    {
        return response()->json(
            [
                'status' => true,
                'error' => 'N/A',
                'message' => 'edit',
                'data' => [],
            ],
            200
        );
    }


    public function update(Request $req)
    {



        $appConfiguration = \Illuminate\Support\Facades\DB::table('app_configurations')->where('key', '=', $req->input("key"))->where('value', '=', $req['value']);

        try {
            if ($appConfiguration->exists()) {
                $appConfiguration = \Illuminate\Support\Facades\DB::table('app_configurations')->where('key', '=', $req->input("key"))->where('value', '=', $req['value']);
                $appConfiguration->update(["key" => $req["updated_key"], "value" => $req["updated_value"]]);
                
                $query = \Illuminate\Support\Facades\DB::table('app_configurations')->where(["key" => $req["updated_key"], "value" => $req["updated_value"]])->select('id', 'platform_name', 'key', 'value')->first();
                    

                return response()->json(
                    [
                        'status' => true,
                        'error' => 'N/A',
                        'message' => 'Update successful!',
                        'data' => $query
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => true,
                        'error' => 'N/A',
                        'message' => 'Record not found',
                        'data' => $appConfiguration,
                    ],
                    200
                );
            }
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'status' => true,
                    'error' => $e,
                    'message' => $e->getMessage(),
                    'data' => $appConfiguration,
                ],
                500
            );
        }
    }


    public function destroy(int $id)
    {

        try {
            $appConfiguration =  AppConfiguration::find($id);
            $appConfiguration->delete();


            return response()->json(
                [
                    'status' => true,
                    'error' => 'N/A',
                    'message' => 'Configuration Data Is Deleted',
                    'data' => $appConfiguration,
                ],
                200
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'status' => true,
                    'error' => $e,
                    'error_message' => $e->getMessage(),
                    'message' => 'Something went wrong',
                    'data' => [],
                ],
                500
            );
        }
    }
}
