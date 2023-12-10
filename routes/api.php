<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\FireAuthUserController;
use App\Http\Controllers\AppConfigurationController;
use App\Http\Controllers\UserFriendListController;
use App\Http\Controllers\OneSignalUserProfileController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('registerUser', [FireAuthUserController::class, 'registerUser']);
Route::post('deleteUser', [FireAuthUserController::class, 'deleteUser']);
Route::post('getUserDetail', [FireAuthUserController::class, 'getUserDetail']);
Route::post('updateOnesignalSubcriptionId', [FireAuthUserController::class, 'updateOnesignalSubcriptionId']);
Route::apiResource('appConfiguration', AppConfigurationController::class);
Route::apiResource('userLists', UserFriendListController::class);

Route::post('sendNotification', [OneSignalUserProfileController::class, 'sendNotification']);
Route::post('sendEmail', [FireAuthUserController::class, 'sendEmail']);

Route::post('friendRequest', [FireAuthUserController::class, 'friendRequest']);
Route::post('acceptFriendRequest', [FireAuthUserController::class, 'acceptFriendRequest']);

Route::get('getAllUser', [FireAuthUserController::class, 'getAllUser']);

Route::post('invitationList', [FireAuthUserController::class, 'invitationList']);
Route::post('requestList', [FireAuthUserController::class, 'requestList']);

Route::post('cancelInvite', [FireAuthUserController::class, 'cancelInvite']);
