<?php
namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use Config;
use App\Models\Therapist;
use App\User;


class AppID {


    /*
        GET Cloud IAM Token and store to config
    */
    public static function getCloudIAMToken(){
        try{
            $url = Config::get('appid.ibmcloud_iam_server_host').'/oidc/token';
            $client = new Client();
            $response = $client->post($url,
                [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept'     => 'application/json',
                    ],
                    'form_params'=> [
                        'grant_type'=>'urn:ibm:params:oauth:grant-type:apikey',
                        'apikey' => Config::get('appid.ibmcloud_api_key')
                    ]
                ]
            );

            $res_body =  \json_decode($response->getBody());
            // Config::set('appid.ibmcloud_iam_token',$res_body->access_token);
            config(['appid.ibmcloud_iam_token'=> $res_body->access_token]);
            return response()->json($res_body, 200);
        }
        catch(ClientException $e){
            return response()->json([
                'status' => array('code' => 500, 'message' => $e->getMessage())
            ], 500);
        }
    }

    public static function getCloudIAMAccessToken(){
        try{
            $url = Config::get('appid.ibmcloud_iam_server_host').'/oidc/token';
            $client = new Client();
            $response = $client->post($url,
                [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept'     => 'application/json',
                    ],
                    'form_params'=> [
                        'grant_type'=>'urn:ibm:params:oauth:grant-type:apikey',
                        'apikey' => Config::get('appid.ibmcloud_api_key')
                    ]
                ]
            );

            $res_body =  \json_decode($response->getBody());
            // Config::set('appid.ibmcloud_iam_token',$res_body->access_token);
            config(['appid.ibmcloud_iam_token'=> $res_body->access_token]);
            return $res_body->access_token;
        }
        catch(ClientException $e){
            return response()->json([
                'status' => array('code' => 500, 'message' => $e->getMessage())
            ], 500);
        }
    }

    /*
    -------------------------------------------------------------
       A.  MANAGEMENT
    -------------------------------------------------------------
    */

    /*
    -------------------------------------------------------------
       A.1 CLOUD DIRECTORY
    -------------------------------------------------------------
    */


    /*
        GET CLOUD DIRECTORY USERS
        note :
        1. paginated
    */

    public static function getCloudDirectoryUsers(){
        $access_token = config('appid.ibmcloud_iam_token');

        try{
            if(empty($access_token)){
                $access_token = self::getCloudIAMAccessToken();

            }
            $url = Config::get('appid.appid_management_server_host').'/management/v4/'.Config::get('appid.appid_tenant_id').'/cloud_directory/Users';
            $client = new Client();
            $header = [
                'Authorization' => 'Bearer '.$access_token,
                'Accept'     => 'application/json',
            ];
            $response = $client->get($url,[
                    'headers' => $header
                ]
            );

            $res_body =  \json_decode($response->getBody());
            return response()->json($res_body, 200);
        }
        catch(ClientException $e){
            return response()->json([
                'status' => array('code' => 500, 'message' => $e->getMessage())
            ], 500);
        }
    }

    /*
        POST CLOUD DIRECTORY SIGN UP
    */

    public static function cloudDirectorySignUp(Request $data){
        $access_token = config('appid.ibmcloud_iam_token');

        try{
            if(empty($access_token)){
                $access_token = self::getCloudIAMAccessToken();
            }

            $user = [
                    "active" => true,
                    "emails" => [[
                        "value" => $data['email'],
                        "primary" => true
                    ]],
                    "name" => [
                        "givenName" => $data['first_name'],
                        "familyName" => $data['last_name'],
                        "formatted" => $data['first_name'].' '.$data['last_name']
                    ],
                    "password" => $data['password'],
                    "roles" => [
                        [ "value"  => $data['role'] ],
                    ]
                ];

            // return $user;

            $url = Config::get('appid.appid_management_server_host').'/management/v4/'.Config::get('appid.appid_tenant_id').'/cloud_directory/sign_up?shouldCreateProfile=true';
            $client = new Client();
            $header = [
                'Authorization' => 'Bearer '.$access_token,
                'Accept'     => 'application/json',
                'Content-Type' => 'application/json'
            ];
            $response = $client->post($url,
                [
                    'headers' => $header,
                    'json' => $user
                ]
            );

            $res_body =  \json_decode($response->getBody());
            $newUser = [];
            $newUser['user_id'] = $res_body->profileId;
            $newUser['email'] = $data['email'];
            $newUser['group_id'] = 2;

            $vUser = User::create($newUser);

            $therapist = [];
            $therapist['user_id'] = $vUser->id;
            $therapist['first_name'] = $data['first_name'];
            $therapist['last_name'] = $data['last_name'];
            $therapist['gender'] = $data['gender'] ? $data['gender'] : 'male';
            $therapist['hospital_id'] = $data['hospital_id'];
            $therapist['therapy_category_id'] = $data['therapy_category_id'];

            Therapist::create($therapist);



            $res = [
                'status'=> [
                        'code'=>200,
                        'message'=>'Success Signed Up'
                    ]
            ];
            return response()->json($res, 200);
        }
        catch(ClientException $e){
            $response =  $e->getResponse()->getBody()->getContents();

            return response()->json([
                'status' => array('code' => $e->getResponse()->getStatusCode(), 'message' => \json_decode($response)->detail)
            ], $e->getResponse()->getStatusCode());
        }
    }

    /*
    -------------------------------------------------------------
       B.  OAUTH V4
    -------------------------------------------------------------
    */


    /*
        POST Login by password
        @param grant_type => password
        @param email
        @param password
    */

    public static function login(Request $request){
        try{

            $user = [
                'grant_type' => 'password',
                'username' => $request->email,
                'password' => $request->password
            ];

            $url = Config::get('appid.appid_auth_server_host').'/oauth/v4/'.Config::get('appid.appid_tenant_id').'/token';
            $client = new Client();
            $uname = config('appid.appid_client_id');
            $pwd = config('appid.appid_secret');
            $creds = base64_encode($uname.':'.$pwd);
            $header = [
                'Authorization' => 'Basic '.$creds,
                'Accept'     => 'application/json',
                'Content-Type' => 'application/json'
            ];
            $response = $client->post($url,
                [
                    'headers' => $header,
                    'json' => $user
                ]
            );

            $res_body =  \json_decode($response->getBody());

            $data = self::getUserInfo($res_body->access_token);
            $res = [
                'status'=> [
                        'code'=>200,
                        'message'=>'Success Login'
                    ],
                'data' =>[
                    'access_token' => $res_body->access_token,
                    'user'=>$data->getData()
                ]
            ];
            return response()->json($res, 200);
        }
        catch(ClientException $e){
            $response =  $e->getResponse()->getBody()->getContents();

            return response()->json([
                'status' => array('code' => $e->getResponse()->getStatusCode(), 'message' => \json_decode($response)->error_description)
            ], $e->getResponse()->getStatusCode());
        }
    }

    public static function grantByPassword(Request $request){
        try{

            $user = [
                'grant_type' => 'password',
                'username' => $request->email,
                'password' => $request->password
            ];

            $url = Config::get('appid.appid_auth_server_host').'/oauth/v4/'.Config::get('appid.appid_tenant_id').'/token';
            $client = new Client();
            $uname = config('appid.appid_client_id');
            $pwd = config('appid.appid_secret');
            $creds = base64_encode($uname.':'.$pwd);
            $header = [
                'Authorization' => 'Basic '.$creds,
                'Accept'     => 'application/json',
                'Content-Type' => 'application/json'
            ];
            $response = $client->post($url,
                [
                    'headers' => $header,
                    'json' => $user
                ]
            );

            $res_body =  \json_decode($response->getBody());
            return response()->json($res_body, 200);
        }
        catch(ClientException $e){
            $response =  $e->getResponse()->getBody()->getContents();

            return response()->json([
                'status' => array('code' => $e->getResponse()->getStatusCode(), 'message' => \json_decode($response)->error_description)
            ], $e->getResponse()->getStatusCode());
        }
    }

    public static function getLoginPage(){
            $url = Config::get('appid.appid_auth_server_host').'/oauth/v4/'.Config::get('appid.appid_tenant_id').'/authorization?client_id='.Config::get('appid.appid_client_id').'&response_type=code&redirect_uri=http://localhost/auth/user';
            return $url;
    }

    public static function getCloudDirectoryForgotPasswordPage(){
            $url = Config::get('appid.appid_auth_server_host').'/oauth/v4/'.Config::get('appid.appid_tenant_id').'/cloud_directory/forgot_password?client_id='.Config::get('appid.appid_client_id').'&redirect_uri='.Config::get('appid.appid_redirect_uri');
            return $url;
    }

    public static function getUserInfo($accessToken){
        try{

            $url = Config::get('appid.appid_auth_server_host').'/oauth/v4/'.Config::get('appid.appid_tenant_id').'/userinfo';
            $client = new Client();
            $header = [
                'Authorization' => 'Bearer '.$accessToken,
                'Accept'     => 'application/json',
            ];
            $response = $client->get($url,[
                    'headers' => $header
                ]
            );

            $res_body =  \json_decode($response->getBody());
            $user = [];
            $vFind = User::where('user_id',$res_body->sub)->first();
            if($vFind){
                $user['id'] = $vFind->id;
            }
            $user['user_id'] = $res_body->sub;
            $user['email'] = $res_body->email;
            $user['name'] = $res_body->name;
            $user['first_name'] = $res_body->given_name;
            $user['last_name'] = $res_body->family_name;
            $user['access_token'] = $accessToken;
            return response()->json($user, 200);
        }
        catch(ClientException $e){
            $response =  $e->getResponse()->getBody()->getContents();;
            return response()->json([
                'status' => array('code' => $e->getResponse()->getStatusCode(), 'message' => \json_decode($response)->error_description)
            ], $e->getResponse()->getStatusCode());
        }
    }

    public static function check(){
        $userToken = session()->get('user',null);
        return $userToken;

        // $user = \App\Models\Therapist::first();
        // return $user;
    }

    public static function logout(){
        if(session()->has('user')){
            session()->forget('user'); // remove email session
            return true;
        }
        return false;
    }


}
