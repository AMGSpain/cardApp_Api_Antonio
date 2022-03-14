<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Models\User;



class ValidarPermisosUsuario
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

        if($user->role =='Administrador'){
            $response['msg'] = "Permisos de administrador validados";
            return $next($request);

        }else{
            $response['status'] = 0;
            $response['msg'] = "No cuenta con permisos para ejecutar esta funcion";
        }
        return response()->json($response);

    }
}
