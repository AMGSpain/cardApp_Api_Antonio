<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ValidarPermisosVenta
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
        $response = ["status" => 1, "msg" => ""];

        $jsonData = $request->getContent();
        $data = json_decode($jsonData);
        $user = User::where('token', $data->token_val)->first();

        if($user->role =='Particular'){
            $response['msg'] = "Permisos de usuario valido";
            return $next($request);

        }else{
            $response['status'] = 0;
            $response['msg'] = "No tiene permisos de usuario para esta acción";
        }
        return response()->json($response);
    }
}
