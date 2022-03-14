<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Cards;
use App\Models\Collections;
use App\Mail\OrderShipped;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Bootstrap\ConfigureLogging;
//use Monolog\Logger;
//use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;



class UserController extends Controller
{
    public function register(Request $request){


        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make($request->all(),
        [
            "name"=>["required","unique:App\Models\User,name","max:50"],
            "email"=>["required","email","unique:App\Models\User,email","max:50"],
            "password"=>["required","regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/"],
            "role"=>["required",Rule::in(['Particular', 'Profesional', 'Administrador'])],
        ]);

        if ($validator->fails()){
            $response['status'] = 0;
            $response['msg'] = $validator->errors();

        }else{

            $jsonData = $request->getContent();
            $data = json_decode($jsonData);
            $user = new User;

            $user->name = $data->name;
            $user->email = $data->email;
            $user->role = $data->role;
            $user->password = Hash::make($data->password);

            try {
                $user->save();
                $response["msg"] = $user;
                Log::info('Se ha hecho un registro con el ID:', ['id' => $user->id]);
            } catch (\Exception $e) {
                $response["msg"] = "Error en el registro de: ".$e;
                Log::error('Error en hacer un registro');
            }

        }
        return response()->json($response);

    }

    public function login(Request $request){
        $jsonData = $request->getContent();
        $data = json_decode($jsonData);
        $user = User::where('name',$data->name)->first();
        if($user){
            $response["msg1"]="nombre coincide";

            if(Hash::check($data->password, $user->password)){
                $user->token = Hash::make($data->name);
                $user->save();
                $response["msg2"]="contraseña coincide";
                $response["msg3"]=$user->token;
                Log::info('Ha hecho login', ['id' => $user->id]);
            }else{
                $response["msg4"]="contraseña no coincide";
            }
        }else{
            $response["msg5"]="nombre no coincide";
        }

        return response()->json($response);
    }

    public function profile(Request $request){
        $jdata = $request->getContent();
        $data = json_decode($jdata);

        $user = User::where('token',$data->token_val)->get();
        $response['msg1']=$user;

        return response()->json($response);

    }


    public function forgotPassword(Request $request){
        $jsonData = $request->getContent();
        $data = json_decode($jsonData);
        $user = User::where('email',$data->email)->first();

        try {
            if($user){
                $characters = "abcdefghijklamnopqxyzABCDEFGHIJKLMNOPQXYZ123456789";
                $charactersLength = strlen($characters);
                $newpassword = "";
                for ($i=0; $i < 9; $i++) {
                    $newpassword .= $characters[rand(0, $charactersLength - 1)];

                }

                $response['msg'] ="Tu nuevo password es: ".$newpassword;
                $user->password = Hash::make($newpassword);
                $user->save();
            }else{
                $response['msg2'] = "usuario no registrado";
            }
        } catch (\Exception $e) {
            $response['msg3'] = "Se ha producido un error: ".$e->getMessage();
            Log::error('Error en Recuperación de password');
        }
        Log::info('Ha hecho Recuperación de password ID:', ['id' => $user->id]);
        return response()->json($response);
    }

}
