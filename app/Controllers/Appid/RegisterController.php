<?php

namespace App\Http\Controllers\Appid;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use AppID;
use Validator;

class RegisterController extends Controller
{
    public function register(Request $r){
        $validator = Validator::make($r->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'first_name'=> 'required',
            'last_name' => 'required',
            'role' => 'required',
            // 'gender' => 'required',
            'hospital_id' => 'required|numeric',
            'therapy_category_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => array('code' => 400, 'message' => trans('messages.validation')),
                'data' => array('field_errors' => $validator->errors())
            ], 400);
        } else {
            return AppID::cloudDirectorySignUp($r);
        }
    }
}
