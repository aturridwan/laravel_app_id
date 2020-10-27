<?php

namespace App\Http\Middleware;

use Closure;

class AppIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($r, Closure $next)
    {
           if($r->bearerToken()){
               $bearerToken = $r->bearerToken();
               $user = \AppID::getUserInfo($bearerToken);
               $status = $user->getStatusCode();
               if ($status==200){
                    $r->session()->put('user',$user->getData());
                    return $next($r);
                } else {
                    return response()->json([
                        'status'    => ['code' => $status, 'message' => $user->getData()->status->message],
                    ],$status);
                }
           }
           else{
                return response()->json([
                    'status'    => ['code' => 401, 'message' => 'token is invalid'],
                ],401);
           }

    }
}
