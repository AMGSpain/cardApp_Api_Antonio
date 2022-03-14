<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Cards;
use App\Models\Collections;
use App\Models\CardsOnSale;
use App\Models\CardsForCollections;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CollectionsController extends Controller
{
    public function altaColeccion(Request $request){
        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($request->getContent(), true), [
            'collection_name' => ['required', 'max:50'],
            'symbol' => ['required', 'max:100'],
            'dateEdition' => ['required', 'date'],
            'id' => ['required', 'max:100'],
        ]);

        if ($validator->fails()) {
            $response['status'] = 0;
            $response['msg'] = $validator->errors();
        } else {
        //dd($request);
        $datos = $request->getContent();

        $datos = json_decode($datos);

        $userToken = User::where('token', $datos->token_val)->first();

        if($datos){

            $collectionBase = Collections::where('collection_name', $datos->collection_name)->get()->first();

            if($collectionBase){
                $response = response()->json(['Failure' => 'La coleccion con el nombre dado ya existe']);
            }else{
                $collection = new Collections();
                $collection->collection_name=$datos->collection_name;
                $collection->dateEdition=$datos->dateEdition;
                $collection->symbol=$datos->symbol;
                $collection->user_id = $userToken->id;

                $card = new Cards();
                $card->card_name = 'default';
                $card->description='default';
                $card->collection = $collection->collection_name;
                $card->user_id = $userToken->id;

                try{
                    $collection->save();
                    $card->save();
                    $response['msg'] = "Colección y Cartas creadas con el Id: ";

                } catch(\Exception $e){
                    $response['msg'] = "Se ha producido un error: ".$e->getMessage();
                }

                $cardCollection = new CardsForCollections();
                $cardCollection->card_id = $card->id;
                $cardCollection->collection_id = $collection;

                try{
                    $cardCollection->save();
                    $response = response()->json(['Success' => 'tabla intermedia completa']);

                }catch(\Exception $e){
                    $response['msg'] = "Se ha producido un error: ".$e->getMessage();
                }

                $response['msg'] = "Colección creada con el Id: ".$collection->id;
            }
        }else{
            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error: ".$e->getMessage();
        }
    }

        return response()->json($response);
    }


    public function cambiarColeccion(Request $request)
    {
        $response = ["status" => 1, "msg" => ""];

        $datos = $request->getContent();

        $datos = json_decode($datos);

        $userToken = User::where('token', $datos->token_val)->first();


            if($datos){
                $cardDB = Cards::find($datos->card_id);

                if($cardDB){
                    $cardDB->collection = $datos->collection_name;
                    $cardDB->user_id = $userToken->id;

                    try{
                        $cardDB->save();
                        $response['msg'] = 'Carta modificada';
                    }catch(\Exception $e){
                        $response['msg'] = "Se ha producido un error: ".$e->getMessage();
                    }
                }else{
                    $response['msg'] = 'Carta no encontrada';
                }
            }else{
                $response['status'] = 0;
                $response['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
        return response()->json($response);
    }

}
