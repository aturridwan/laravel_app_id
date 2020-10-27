<?php

namespace App\Http\Middleware;

use Closure;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ClientErrorResponseException;
use App\User;

class hasAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        try {
            $userId = \AppID::check()->id;
            // dd($userId);
            $user = User::find($userId);
            if($user){
                $group = $user->group;
                if($group->group_name != $role){
                    return \Response::json([
                        'status' => array('code' => 401, 'message' => 'You Didn\'t Have A Permission')
                    ], 401);
                }
            }
        } catch (\Throwable $e) {
            return \Response::json([
                'status' => array('code' => 404, 'message' => $e->getMessage())
            ], 404);
        }
        return $next($request);

    }
    /* public function handle($request, Closure $next, $role)
    {
        try {
            $token = $request->token;
            $url = 'http://localhost:8000/api/v1/check/check-access/' . $role . '?token=' . $token;

            $client = new Client();
            $response = $client->get($url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept'     => 'application/json',
                    ]
                ]
            );
            $res_code = $response->getStatusCode();
            $res_body = $response->getBody()->getContents();
            $arr_res_body = json_decode($res_body, true);
            if ($res_code == 200) {
                return $next($request);
            }
        } catch (\Throwable $e) {
            $body = $e->getResponse()->getBody();
            return response()->json(json_decode($body));
        }

    } */
}
