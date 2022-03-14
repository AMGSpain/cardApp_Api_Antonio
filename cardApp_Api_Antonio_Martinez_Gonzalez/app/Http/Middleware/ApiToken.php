<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\UserController;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(isset($request->token_val)){
            $apitoken = $request->token_val;

            if($user = User::where('token',$apitoken)->first()){
                $response["msg"] = "Api token valido";
                $request->user = $user;
                return $next($request);
            }else{
                $response["status"] = 0;
                $response["msg"] = "Token invalido";
            }

        }else{
            $response["status"] = 0;
            $response["msg"] = "Token no ingresado";
        }

        return response()->json($response);

    }
}
