<?php

namespace App\Http\Controllers\Appid;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use AppID;
use App\Models\Therapist;
use Response;
use Validator;
use App\User;

class LoginController extends Controller
{

    public function login(Request $r){
        $validator = Validator::make($r->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => array('code' => 400, 'message' => trans('messages.validation')),
                'data' => array('field_errors' => $validator->errors())
            ], 400);
        } else {

            $resloginAppId = AppID::login($r);
            if($resloginAppId->getStatusCode() == 200){
                $loginAppid = $resloginAppId->getData();
                $user = User::where('user_id',$loginAppid->data->user->user_id)->first();
                if($user){
                    $group = $user->group;
                    if($group->group_name == $r->role){
                        return $resloginAppId;
                    }
                    else{
                        return Response::json([
                            'status' => array('code' => 203, 'message' => "Sorry, the information you entered does not match any ".ucfirst($r->role)." account.  Please check and try again."),
                        ], 203);
                    }
                }
                else{
                    return Response::json([
                        'status' => array('code' => 404, 'message' => "Sorry, the information you entered does not match any account."),
                    ], 404);
                }
            }

            return $resloginAppId;


        }
    }

    public function loginAdmin(Request $r){

        $validator = Validator::make($r->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('login')
                        ->withErrors($validator)
                        ->withInput();
        }


            $resloginAppId = AppID::login($r);
            if($resloginAppId->getStatusCode() == 200){
                $loginAppid = $resloginAppId->getData();
                $user = User::where('user_id',$loginAppid->data->user->user_id)->first();
                if($user){
                    $group = $user->group;
                    if($group->group_name == $r->role){
                        // return $resloginAppId;
                         session()->put('user',$loginAppid->data->user);
                         return redirect()->route('exercise.index');
                    }
                    else{
                        // return Response::json([
                        //     'status' => array('code' => 203, 'message' => "Sorry, the information you entered does not match any ".ucfirst($r->role)." account.  Please check and try again."),
                        // ], 203);
                        session()->flash("error","Sorry, the information you entered does not match any ".ucfirst($r->role)." account.  Please check and try again.");
                        return redirect()->route('login');
                    }
                }
                else{
                    session()->flash("error","Sorry, the information you entered does not match any ".ucfirst($r->role)." account.  Please check and try again.");
                    return redirect()->route('login');
                }
            }

            // return $resloginAppId;
            session()->flash('error',$resloginAppId->getData()->status->message);
            return redirect()->route('login');

    }

    public function logoutAdmin(Request $r){
        $logout = AppID::logout();
        if($logout){
            return redirect()->route('login');
        }
        else{
            return redirect('/');
        }
    }

    public function getForgotPasswordLink(){
        try{

        return response()->json([
            'status'=>[
                'code' => 200,
                'message' => 'Success get forgot password link.'
            ],
            'data' =>[
                'forgot_password_link' =>AppID::getCloudDirectoryForgotPasswordPage()
            ]
        ],200);
        }
        catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'status' => [
                    'code'=>500,
                    'message' => 'Server Error'
                ]
            ],500);
        }
    }

}
