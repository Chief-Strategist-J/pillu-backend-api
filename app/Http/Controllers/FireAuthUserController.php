<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use App\Mail\TestEmail;
use App\Models\FireAuthUser;
use App\Models\OneSignalUserProfile;
use App\Models\User;
use App\Models\RequestList;
use App\Models\InvitationList;
use App\Models\UserFriendList;
use App\Http\Resources\UserResource;
use App\Http\Resources\RequestListResource;
use App\Http\Resources\InvitationListResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Helper\Helper;
use Throwable;

class FireAuthUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function sendEmail()
    {
        try {
            //
        } catch (Throwable $e) {
            //
        }
    }

    public function updateOnesignalSubcriptionId(Request $request): \Illuminate\Http\JsonResponse
    {
        try {

            $user = User::where('email', "=", $request->input('email'))->first();
            $onesignal_subscription_id = $request->input('onesignal_subscription_id');


            if (!is_null($user) && $user->exists()) {
                $update_onesignal_subsciption_id = [
                    'onesignal_subscription_id' => $onesignal_subscription_id,
                ];

                $user->oneSignalProfile()->update($update_onesignal_subsciption_id);

                $data = [
                    'email' => $user->email,
                    'onesignal_subscription_id' => $onesignal_subscription_id
                ];

                return Helper::successMessage(
                    message: 'OneSignal Subscription id is successfully updated',
                    data: $data
                );
            } else {
                return Helper::errorMessage(
                    message: 'OneSignal Subscription Id Is Not updated',
                    error: "No User Found"
                );
            }
        } catch (Throwable $e) {
            return Helper::errorMessage(
                message: $e->getMessage(),
                error: $e
            );
        }
    }

    public function registerUser(Request $request): \Illuminate\Http\JsonResponse
    {
        try {


            $email = $request->input('email');
            $user_is_already_exists = User::where('email', $email);

            if ($user_is_already_exists->exists()) {
                $userInfo = DB::table('fire_auth_users')->where('email', '=', $request->input('email'))->first();
                $record = User::with('fireAuthUser')->find($userInfo->id);

                if (!is_null($record) && $record->exists()) {

                    return Helper::successMessage(
                        message: 'Successful',
                        data: new UserResource($record)
                    );
                } else {
                    return Helper::errorMessage(
                        message: "No User Found",
                        error: 'Something went wrong'
                    );
                }
            } else {
                $validate_req = [
                    'name' => 'required|string',
                    'userName' => 'required|string',
                    'firstname' => 'nullable|string',
                    'lastname' => 'nullable|string',
                    'email' => 'required|email|unique:users,email',
                    'firebase_user_id' => 'required|string',
                    'password' => 'required|string',
                    'photoUrl' => 'required|string',
                ];

                $request->validate($validate_req);

                $name = $request->input('name');
                $email = $request->input('email');
                $password = $request->input('password');
                $photo_url = $request->input('photoUrl');
                $user_name = $request->input('userName');
                $firebase_user_id = $request->input('firebase_user_id');
                $firstname = $request->input('firstname');
                $lastname = $request->input('lastname');
                $onesignal_subscription_id = $request->input('onesignal_subscription_id');
                $onesignal_user_token = $request->input('onesignal_user_token');
                $onesignal_external_id = $request->input('onesignal_external_id');


                $user_req = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                ];

                $user = User::create($user_req);
                $user_id = $user->id;

                $create_auth_req = [
                    'user_id' => $user_id,
                    'photoUrl' => $photo_url,
                    'email' => $email,
                    'userName' => $user_name,
                    'firebase_user_id' => $firebase_user_id,
                    'password' => $password,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                ];

                $one_signal_profile_req = [
                    'user_id' => $user->id,
                    'onesignal_subscription_id' => $onesignal_subscription_id,
                    'onesignal_email' =>  $email,
                    'onesignal_user_token' => $onesignal_user_token,
                    'onesignal_external_id' => $onesignal_external_id,
                ];

                $firebase_authenticated_user = FireAuthUser::create($create_auth_req);
                $one_signal_user = OneSignalUserProfile::create($one_signal_profile_req);

                $user->fireAuthUser()->save($firebase_authenticated_user);
                $user->oneSignalProfile()->save($one_signal_user);

                event(new Registered($user));

                Auth::login($user);
                $userWithFireAuthUser = User::with('fireAuthUser')->find($user->id);

                return Helper::successMessage(
                    message: 'Account created successfully!',
                    data: new UserResource($userWithFireAuthUser),
                );
            }
        } catch (Throwable $e) {
            report($e);
            $this->logMessage($e);

            return Helper::errorMessage(
                message: $e->getMessage(),
                error: $e
            );
        }
    }

    public function logMessage($e)
    {

        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();
        $slack_error_message = "Something happened! \n\n\n ERROR MESSAGE: $errorMessage\n\n\n CODE: $errorCode\n\n";
        Log::channel('slack')->info($slack_error_message);
    }

    public function deleteUser(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $record = User::find($request->input('id'));

            if (Helper::isObjectExist($record)) {
                $record->delete();
                return Helper::successMessage(message: 'Account Deleted Successfully!', data: 'N/A');
            } else {
                return Helper::successMessage(message: 'Record not found', data: 'N/A');
            }
        } catch (Throwable $e) {
            report($e);
            $this->logMessage($e);

            return Helper::errorMessage(message: $e->getMessage(), error: $e);
        }
    }


    public function getAllUser(): \Illuminate\Http\JsonResponse
    {
        try {
            return Helper::successMessage(message: 'Successful', data: User::all());
        } catch (Throwable $e) {
            report($e);
            $this->logMessage($e);

            return Helper::errorMessage(message: $e->getMessage(), error: $e);
        }
    }

    public function getUserDetail(Request $request)
    {
        try {

            $user_info = DB::table('fire_auth_users')->where('firebase_user_id', '=', $request->input('firebase_user_id'))->first();
            $record = User::with('fireAuthUser')->find($user_info->id);

            if (Helper::isObjectExist($record)) {
                return Helper::successMessage(message: 'Successful', data: new UserResource($record));
            } else {
                return Helper::successMessage(message: 'Record not found', data: 'N/A');
            }
        } catch (Throwable $e) {
            report($e);
            $this->logMessage($e);
            return Helper::errorMessage(message: $e->getMessage(), error: $e);
        }
    }


    public function friendRequest(Request $request)
    {
        try {
            $inviter = $request->input('inviter_email');
            $inviter_record = User::where('email', $inviter)->first();
            $invitee = $request->input('invitee_email');
            $invitee_record = User::where('email', $invitee)->first();

            if ($inviter === $invitee) {
                return Helper::errorMessage(message: "Friend Request Failed.", error: "Same Email Found In Request");
            }

            if (!Helper::isObjectExist($inviter_record)) {
                return Helper::errorMessage(message: "Friend Request Failed", error: "Inviter Is Not Exists");
            }

            if (!Helper::isObjectExist($invitee_record)) {
                return Helper::errorMessage(message: "Friend Request Failed.", error: "Invitee Is Not Exists");
            }

            $invitee_exists_in_inviters_invitation_list = $inviter_record->userInvitations()->where('invitation_email', $invitee)->exists();
            $invitee_exists_in_inviters_request_list = $inviter_record->userRequests()->where('requested_email', $invitee)->exists();
            $invitee_exists_in_inviters_freind_list = $inviter_record->userFreinds()->where('email', $invitee)->exists();
            $inviter_exists_in_invitee_invitation_list = $invitee_record->userInvitations()->where('invitation_email', $inviter)->exists();
            $inviter_exists_in_invitee_request_list = $invitee_record->userRequests()->where('requested_email', $inviter)->exists();
            $inviter_exists_in_invitee_user_list = $invitee_record->userFreinds()->where('email', $inviter)->exists();

            $request_is_already_sended =
                $invitee_exists_in_inviters_invitation_list
                || $invitee_exists_in_inviters_request_list
                || $invitee_exists_in_inviters_freind_list
                || $inviter_exists_in_invitee_invitation_list
                || $inviter_exists_in_invitee_request_list
                || $inviter_exists_in_invitee_user_list;

            if ($request_is_already_sended) {
                return Helper::errorMessage(message: "Friend Request Failed.", error: "You May Already Freind Or You Sended Request Earlier Please Check It Again");
            }

            $requested_user_req = ['user_id' => $inviter_record->id, 'requested_email' =>  $invitee];
            $inviter_user_req = ['user_id' => $invitee_record->id, 'invitation_email' => $inviter];

            $requested_user = RequestList::create($requested_user_req);
            $inviter_user = InvitationList::create($inviter_user_req);

            $inviter_record->userRequests()->save($requested_user);
            $invitee_record->userInvitations()->save($inviter_user);

            return Helper::successMessage(
                message: "Successful",
                data: [
                    'inviter_user' => $inviter_record->email,
                    'invitee_user' => $invitee_record->email,
                ],
            );
        } catch (Throwable $e) {
            report($e);
            $this->logMessage($e);
            return Helper::errorMessage(message: $e->getMessage(), error: $e);
        }
    }

    public function acceptFriendRequest(Request $req)
    {

        try {
            $accepter_email = $req->input('accepter_email');
            $inviter_email = $req->input('inviter_email');

            if ($accepter_email === $inviter_email) {
                return Helper::errorMessage(message: "Accept Friend Request Failed.", error: "Same Email Found In Request");
            }

            $accepter_user = User::where('email', '=', $accepter_email)->first();
            $inviter_user = User::where('email', '=', $inviter_email)->first();

            if (!Helper::isObjectExist($accepter_user)) {
                return Helper::errorMessage(message: "Request Failed", error: "Accepter Is Not Exists");
            }

            if (!Helper::isObjectExist($inviter_user)) {
                return Helper::errorMessage(message: "Friend Request Failed.", error: "Inviter Is Not Exists");
            }

            $is_user_exists_to_accept = $accepter_user->userInvitations()->where('invitation_email', $inviter_email)->exists();
            $is_user_sended_freind_request = $inviter_user->userRequests()->where('requested_email', $accepter_email)->exists();

            if (!$is_user_exists_to_accept) {
                return Helper::errorMessage(message: "Accept Request Failed.", error: "Not Found In Invitation List");
            }

            if (!$is_user_sended_freind_request) {
                return Helper::errorMessage(message: "Accept Request Failed.", error: "User Not Sended Freind Request");
            }

            $accepter_user->userRequests()->where('requested_email', $inviter_email)->delete();
            $accepter_user->userInvitations()->where('invitation_email', $inviter_email)->delete();

            $inviter_user->userRequests()->where('requested_email', $accepter_email)->delete();
            $inviter_user->userInvitations()->where('invitation_email', $accepter_email)->delete();

            $is_valid_to_accept = !($accepter_user->userFreinds()->where('email', $inviter_email)->exists());

            if ($is_valid_to_accept) {

                $requested_user = [
                    'user_id' => $accepter_user->id,
                    'email' => $inviter_email,
                    'is_accepted' =>  false,
                    'is_blocked' => false,
                ];

                $requested_user = UserFriendList::create($requested_user);
                $accepter_user->userFreinds()->save($requested_user);
            }

            $is_user_willing_to_accept = !($inviter_user->userFreinds()->where('email', '=', $accepter_email)->exists());

            if ($is_user_willing_to_accept) {
                $user_freind_req = [
                    'user_id' => $inviter_user->id,
                    'email' => $accepter_email,
                    'is_accepted' =>  false,
                    'is_blocked' => false,
                ];

                $requested_user = UserFriendList::create($user_freind_req);
                $inviter_user->userFreinds()->save($requested_user);
            }

            return Helper::successMessage(
                message: "Successful",
                data: [
                    'accepter_email' => $accepter_user->email,
                    'inviter_email' => $inviter_user->email,
                ],
            );
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return Helper::errorMessage(
                message: $e->getMessage(),
                error: $e,
            );
        }
    }


    public function invitationList(Request $req)
    {
        try {
            $record = User::where('email', '=', $req->input('email'))->first();

            if (!is_null($record) && $record->exists()) {
                return Helper::successMessage(message: 'Successful', data: new InvitationListResource($record));
            } else {
                return Helper::successMessage(message: 'No record Found', data: 'N/A');
            }
        } catch (Throwable $e) {
            report($e);
            $this->logMessage($e);
            return Helper::errorMessage(message: $e->getMessage(), error: $e);
        }
    }

    public function cancelInvite(Request $req)
    {
        try {
            return Helper::successMessage(message: 'Pending..', data: 'N/A');
        } catch (Throwable $e) {
            report($e);
            $this->logMessage($e);
            return Helper::errorMessage(message: $e->getMessage(), error: $e);
        }
    }

    public function requestList(Request $req)
    {
        try {
            $record = User::where('email', '=', $req->input('email'))->first();

            if (!is_null($record) && $record->exists()) {
                return Helper::successMessage(message: 'Successful', data: new RequestListResource($record));
            } else {
                return Helper::successMessage(message: 'Successful', data: 'No Record Found');
            }
        } catch (Throwable $e) {
            report($e);
            $this->logMessage($e);
            return Helper::errorMessage(message: $e->getMessage(), error: $e);
        }
    }
}
