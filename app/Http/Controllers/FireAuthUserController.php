<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FireAuthUser;
use App\Models\OneSignalUserProfile;
use App\Models\User;
use App\Models\RequestList;
use App\Models\InvitationList;
use App\Models\UserFriendList;
use App\Http\Resources\UserResource;
use App\Http\Resources\RequestListResource;
use App\Http\Resources\InvitationListResource;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FireAuthUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function sendEmail()
    {
        try {
           

            // Use mail function to send the email
            
            Mail::to('dev.jaydeep919@gmail.com')->send(new TestEmail());
            
            Mail::raw("5% off its awesome\n\nGo get it now!", function ($message) {
                $message->from('dev.jaydeep919@gmail.com', 'Company name');
                $message->to('chief.strategist.j@gmail.com');
                $message->subject('5% off all our website');
                $message->html('<p>5% off its awesome</p><p>Go get it now!</p>');
                $message->text("5% off its awesome\n\nGo get it now!");
            });

            
        } catch (Throwable $e) {
            $this->logMessage($e);
            return response()->json(
                [
                    'status' => true,
                    'error' => 'N/A',
                    'message' => 'Sending Email Is Fail',
                    'data' => $e->getMessage(),
                ],
                200
            );
        }
    }

    public function updateOnesignalSubcriptionId(Request $request)
    {
        try {

            $userIsAlreadyExist = User::where('email', "=", $request->input('email'))->first();

            if ($userIsAlreadyExist && $userIsAlreadyExist->exists()) {
                $userIsAlreadyExist->oneSignalProfile()->update(['onesignal_subscription_id' => $request->input('onesignal_subscription_id')]);

                return response()->json(
                    [
                        'status' => true,
                        'error' => 'N/A',
                        'message' => 'OneSignal Subscription id is successfully updated',
                        'data' => [
                            'email' => $userIsAlreadyExist->value('email'),
                            'onesignal_subscription_id' => $userIsAlreadyExist->oneSignalProfile()->value('onesignal_subscription_id')
                        ],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'error' => 'N/A',
                        'message' => 'Something went wrong or No-Email Is Exist',
                        'data' => [],
                    ],
                    404
                );
            }
        } catch (Throwable $e) {
            return response()->json(
                [
                    'status' => false,
                    'error' => $e,
                    'error_message' => $e->getMessage(),
                    'message' => 'Something went wrong',
                    'data' => [],
                ],
                500
            );
        }
    }

    public function registerUser(Request $request)
    {
        try {

            $userIsAlreadyExist = User::where('email', "=", $request->input('email'));

            if ($userIsAlreadyExist->exists()) {
                $userInfo = DB::table('fire_auth_users')->where('email', '=', $request->input('email'))->first();
                $record = User::with('fireAuthUser')->find($userInfo->id);

                if ($record) {

                    return response()->json(
                        [
                            'status' => true,
                            'error' => 'N/A',
                            'message' => 'successful',
                            'data' =>  new UserResource($record),
                        ],
                        200
                    );
                }
            } else {
                $request->validate([
                    'name' => 'required|string',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|string',
                ]);

                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => $request->input('password'),
                ]);

                $request->validate([
                    'photoUrl' => 'required|string',
                    'email' => 'required|email|unique:fire_auth_users,email',
                    'userName' => 'required|string',
                    'firebase_user_id' => 'required|string',
                    'password' => 'required|string',
                    'firstname' => 'nullable|string',
                    'lastname' => 'nullable|string',
                ]);

                // Create a new FireAuthUser instance and save it to the database
                $fireAuthUser = FireAuthUser::create([
                    'user_id' => $user->id,
                    'photoUrl' => $request->input('photoUrl'),
                    'email' => $request->input('email'),
                    'userName' => $request->input('userName'),
                    'firebase_user_id' => $request->input('firebase_user_id'),
                    'password' => $request->input('password'),
                    'firstname' => $request->input('firstname'),
                    'lastname' => $request->input('lastname'),
                ]);

                // Create a new FireAuthUser instance and save it to the database
                $oneSignalUserProfile = OneSignalUserProfile::create([
                    'user_id' => $user->id,
                    'onesignal_subscription_id' => $request->input('onesignal_subscription_id'),
                    'onesignal_email' => $request->input('email'),
                    'onesignal_user_token' => $request->input('onesignal_user_token'),
                    'onesignal_external_id' => $request->input('onesignal_external_id'),
                ]);

                $user->fireAuthUser()->save($fireAuthUser);
                $user->oneSignalProfile()->save($oneSignalUserProfile);

                event(new Registered($user));

                Auth::login($user);
                $userWithFireAuthUser = User::with('fireAuthUser')->find($user->id);

                return response()->json([
                    'status' => true,
                    'error' => 'N/A',
                    'message' => 'Account created successfully!',
                    'data' => new UserResource($userWithFireAuthUser),
                ]);
            }
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return response()->json([
                'status' => false,
                'data' => [],
                'error_message' => $e->getMessage(),
                'message' => 'Error While Registering User!',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function logMessage($e)
    {

        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();

        Log::channel('slack')->info("Something happened! \n\n\n ERROR MESSAGE: $errorMessage\n\n\n CODE: $errorCode\n\n");
    }

    public function deleteUser(Request $request)
    {
        try {
            $record = User::find($request->input('id'));

            if ($record) {
                $record->delete();

                return response()->json(
                    [
                        'status' => true,
                        'error' => 'N/A',
                        'message' => 'Record deleted successfully',
                        'data' =>  [],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => true,
                        'data' => [],
                        'error' => 'N/A',
                        'message' => 'Record not found',
                    ],
                    404
                );
            }
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return response()->json(
                [
                    'status' => false,
                    'data' => [],
                    'message' => 'Error While Deleting User!',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }


    public function getAllUser()
    {
        try {

            return response()->json(
                [
                    'status' => true,
                    'error' => 'N/A',
                    'message' => 'successful',
                    'data' => User::all(),
                ],
                200
            );
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return response()->json(
                [
                    'status' => false,
                    'data' => [],
                    'message' => 'Error While Getting User!',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function getUserDetail(Request $request)
    {
        try {

            $userInfo = DB::table('fire_auth_users')->where('firebase_user_id', '=', $request->input('firebase_user_id'))->first();
            $record = User::with('fireAuthUser')->find($userInfo->id);

            if ($record) {

                return response()->json(
                    [
                        'status' => true,
                        'error' => 'N/A',
                        'message' => 'successful',
                        'data' =>  new UserResource($record),

                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => true,
                        'error' => 'N/A',
                        'message' => 'Record not found',
                        'data' =>  [],
                    ],
                    404
                );
            }
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return response()->json(
                [
                    'status' => false,
                    'data' => [],
                    'message' => 'Error While Getting User!',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }


    public function friendRequest(Request $request)
    {
        try {
            // Current User 
            $inviter = $request->input('inviter_email');
            $inviterRecord = User::where('email', $inviter)->first();

            /// What If Inviter Is Not Exist In Pillu's Record 
            if (is_null($inviterRecord) || !$inviterRecord->exists()) {
                return response()->json(
                    [
                        'status' => false,
                        'data' => [],
                        'message' => "Friend request failed.\nTry again later.",
                        'error' => "Please register first to request",
                    ],
                    403
                );
            }

            // Other User
            $invitee = $request->input('invitee_email');
            $inviteeRecord = User::where('email', $invitee)->first();

            if ($inviterRecord->friendLists()->where('email', '=', $invitee)->exists()) {
                // Add in friend list of invitee if not exist
                return response()->json(
                    [
                        'error' => [],
                        'status' => false,
                        'message' => "Friend request failed.\n Your Already Friend",
                        'data' => $inviterRecord->friendLists()->where('email', '=', $invitee)->first(),
                    ],
                    403
                );
            }


            /// What Is We Are Inviting Is Not In Our Record 
            if (is_null($inviteeRecord) || !$inviteeRecord->exists()) {
                return response()->json(
                    [
                        'status' => false,
                        'data' => [],
                        'message' => "Friend request failed. Try again later.",
                        'error' => "Your friend isn't here(Pillu). Invite them to join for a chat!",
                    ],
                    403
                );
            }

            if ($inviteeRecord->friendLists()->where('email', '=', $inviter)->exists()) {
                // Add in friend list of inviter if not exist
                return response()->json(
                    [
                        'error' => [],
                        'status' => false,
                        'message' => "Friend request failed.\n Your Already Friend",
                        'data' => $inviteeRecord->friendLists()->where('email', '=', $inviter)->get(),


                    ],
                    403
                );
            }

            if ($inviterRecord->requestList()->where('requested_email', $invitee)->exists()) {
                return response()->json([
                    'status' => true,
                    'data' => [],
                    'message' => "The Invitation is Already Sended.",
                    'error' => "",
                ], 200);
            };

            $requestedUser = RequestList::create([
                'user_id' => $inviterRecord->id,
                'requested_email' =>  $invitee,
            ]);

            $inviterUser = InvitationList::create([
                'user_id' => $inviteeRecord->id,
                'invitation_email' => $inviter,
            ]);

            // For Inviter It Will Be Request Becouse He/She Is Requesting 
            $inviterRecord->requestList()->save($requestedUser);

            /// For Invitee Add In Ivitation List
            $inviteeRecord->invitationLists()->save($inviterUser);

            return response()->json(
                [
                    'status' => true,
                    'error' => 'N/A',
                    'message' => 'successful',
                    'current_user' => $inviterRecord,
                    'current_user_requested_list' => $requestedUser,
                    'other_user' => $inviteeRecord,
                    'other_user_invitation_list' => $inviterUser,
                ],
                200
            );
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return response()->json(
                [
                    'status' => false,
                    'data' => [],
                    'message' => "Friend request failed.\nTry again later.",
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function acceptFriendRequest(Request $req)
    {

        try {
            // Current User 
            $accepterEmail = $req->input('accepter_email');
            // Other User
            $inviterEmail = $req->input('inviter_email');

            $accepterUser = User::where('email', '=', $accepterEmail)->first();
            $inviterUser = User::where('email', '=', $inviterEmail)->first();

            $accepterUser->requestList()->where('requested_email', $inviterEmail)->delete();
            $accepterUser->invitationLists()->where('invitation_email', $inviterEmail)->delete();
            $inviterUser->requestList()->where('requested_email', $accepterEmail)->delete();
            $inviterUser->invitationLists()->where('invitation_email', $accepterEmail)->delete();

            if ($accepterUser->friendLists()->where('email', '=', $inviterEmail)->exists()) {
                //
            } else {

                $requestedUser = UserFriendList::create([
                    'user_id' => $accepterUser->id,
                    'email' => $inviterEmail,
                    'is_accepted' =>  false,
                    'is_blocked' => false,
                ]);

                $accepterUser->friendLists()->save($requestedUser);
            }


            if ($inviterUser->friendLists()->where('email', '=', $accepterEmail)->exists()) {
                //
            } else {
                $requestedUser = UserFriendList::create([
                    'user_id' => $inviterUser->id,
                    'email' => $accepterEmail,
                    'is_accepted' =>  false,
                    'is_blocked' => false,
                ]);

                $inviterUser->friendLists()->save($requestedUser);
            }

            return response()->json(
                [
                    'error' => [],
                    'status' => false,
                    'accepter_email' => $accepterUser->email,
                    'inviter_email' => $inviterUser->email,
                    'accepter_friend_data' => $accepterUser->friendLists()->where('email', $inviterUser->email)->first(),
                    'inviter_friend_data' => $inviterUser->friendLists()->where('email', $accepterUser->email)->first(),

                ],
                200,
            );
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return response()->json(
                [
                    'status' => false,
                    'data' => [],
                    'message' => "Make Sure Email Is Correct",
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }


    public function invitationList(Request $req)
    {
        try {
            $record = User::where('email', '=', $req->input('email'))->first();

            if (!is_null($record) && $record->exists()) {
                return response()->json(
                    [
                        'status' => true,
                        'error' => 'N/A',
                        'message' => 'successful',
                        'data' =>  new InvitationListResource($record),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => true,
                        'error' => 'N/A',
                        'message' => 'No record Found',
                        'data' =>  [],
                    ],
                    200
                );
            }
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return response()->json(
                [
                    'status' => false,
                    'data' => [],
                    'message' => "Make Sure Email Is Correct",
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function cancelInvite(Request $req)
    {
        try {
            // Current User 
            $inviter = $req->input('inviter_email');
            // Other User
            $invitee = $req->input('invitee_email');


            $inviterRecord = User::where('email', $inviter)->first();

            /// What If Inviter Is Not Exist In Pillu's Record 
            if (is_null($inviterRecord) || !$inviterRecord->exists()) {
                return response()->json(
                    [
                        'status' => false,
                        'data' => [],
                        'message' => [],
                        'error' => "Please register first to cancel the invitation.",
                    ],
                    403
                );
            }

            $inviteeRecord = User::where('email', $invitee)->first();

            /// What Is We Are Inviting Is Not In Our Record 
            if (is_null($inviteeRecord) || !$inviteeRecord->exists()) {
                return response()->json(
                    [
                        'status' => false,
                        'data' => [],
                        'message' => [],
                        'error' => "Your friend isn't here(Pillu). Invite them to join for a chat!",
                    ],
                    403
                );
            }


            // For Inviter It Will Be Request Becouse He/She Is Requesting 
            $inviterRecord->requestList()->where('requested_email', $invitee)->delete();

            /// For Invitee Add In Ivitation List
            $inviteeRecord->invitationLists()->where('invitation_email', $inviter)->delete();

            return response()->json(
                [
                    'status' => true,
                    'error' => 'N/A',
                    'message' => 'successful',
                    'inviter_user' => $inviterRecord,
                    'invitee_user' => $inviteeRecord,
                    'requested_list' => $inviterRecord->requestList(),
                    'invitation_list' => $inviteeRecord->invitationLists(),
                ],
                200
            );
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return response()->json(
                [
                    'status' => false,
                    'data' => [],
                    'message' => "Make Sure Email Is Correct",
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function requestList(Request $req)
    {
        try {
            $record = User::where('email', '=', $req->input('email'))->first();

            if (!is_null($record) && $record->exists()) {
                return response()->json(
                    [
                        'status' => true,
                        'error' => 'N/A',
                        'message' => 'successful',
                        'data' =>  new RequestListResource($record),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'error' => 'N/A',
                        'message' => 'no record found',
                        'data' => [],
                    ],
                    200
                );
            }
        } catch (Throwable $e) {
            report($e);

            $this->logMessage($e);

            return response()->json(
                [
                    'status' => false,
                    'data' => [],
                    'message' => "Make Sure Email Is Correct",
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
