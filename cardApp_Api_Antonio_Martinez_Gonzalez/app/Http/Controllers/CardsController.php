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
use Illuminate\Support\Facades\Http;

class CardsController extends Controller
{

    public function altaCarta(Request $request){
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

        $datos = $request->getContent();

        $datos = json_decode($datos);

        $userToken = User::where('token', $datos->token_val)->first();

        if ($datos) {
            $collection = Collections::where('collection_name', $datos->collection_name)->get()->first();

            if ($collection) {

                $card=new Cards;
                $card->card_name=$datos->card_name;
                $card->description=$datos->description;
                $card->collection = $collection->collection_name;
                $card->card_price = $datos->card_price;
                $card->edition = $datos->edition;
                $card->imageUrl = $datos->imageUrl;
                $card->user_id = $userToken->id;


                try{
                    $card->save();
                    $response['msg'] = "Carta guardada con id ".$card->id;
                }catch (Exception $e){
                    $response['msg2'] = "Se ha producido un error: ".$e->getMessage();
                }

                $cardCollection = new CardsForCollections();
                $cardCollection->card_id=$card->id;
                $cardCollection->collection_id=$collection->id;
                try{
                    $cardCollection->save();
                    $response['msg'] = "Carta guardada + colección con Id: ".$card->id;
                    $response = response()->json(['Success' => 'Carta guardada con id: '.$card->id]);
                }catch(\Exception $e){

                    $response['msg2'] = "Se ha producido un error: ".$e->getMessage();
                }

            }else{
                $collection = new Collections();
                $collection->collection_name = 'default';
                $collection->symbol = 'default';
                $collection->dateEdition = 'default';
                $collection->user_id = $userToken->id;


                $card = new Cards();
                $card->card_name = $datos->card_name;
                $card->description = $datos->description;
                $card->collection = $collection->collection_name;
                $card->card_price = $datos->card_price;
                $card->edition = $datos->edition;
                $card->imageUrl = $datos->imageUrl;
                $card->user_id = $userToken->id;

               try{

                   $collection->save();
                   $card->save();
                   $response['status'] = 200;
                   $response['msg'] = "Colección creada";

               } catch(\Exception $e){
                    $response['msg3'] = "Se ha producido un error: ".$e->getMessage();
               }

               $cardCollection = new CardsForCollections();
               $cardCollection->card_id = $card->id;
               $cardCollection->collection_id = $collection->id;

               try{

                   $cardCollection->save();
                   $response['status'] = 200;
                   $response['msg'] = "Tabla intermedia creada";

               }catch(\Exception $e){
                $response['msg4'] = "Se ha producido un error: ".$e->getMessage();
               }
            }
        }else{
            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error: ".$e->getMessage();
        }
    }

        return response()->json($response);
    }


    public function buscarCarta(Request $request){
        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($request->getContent(), true), [
            'buscar' => ['required', 'max:200']
        ]);

        if ($validator->fails()) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {
            $datos = $request->getContent();
            $datos = json_decode($datos);

            try{
                $buscar = DB::table('cards_onsale')
                        ->join('users', 'users.id', '=', 'cards_onsale.user_id')
                        ->join('cards', 'cards.id', '=', 'cards_onsale.card_id')
                        ->select('cards.card_name', 'cards_onsale.quantity', 'cards_onsale.price', 'users.name as user'  )
                        ->where('cards.card_name','like','%'. $datos->buscar.'%')
                        ->orderBy('cards_onsale.price','ASC')
                        ->get();
                        $response['carta'] = $buscar;
            }catch(\Exception $e){
                $response['status'] = 0;
                $response['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
        return response()->json($response);
        }
    }

    public function enVenta(Request $request){

        $response = ["status" => 1, "msg" => ""];
        $validator = Validator::make(json_decode($request->getContent(), true), [
            'card_name' => ['required', 'max:100'],
            'price' => ['required', 'min:0'],
            'quantity' => ['required', 'max:100'],
            'id' => ['required', 'max:20'],
            'card_id' => ['required', 'max:20'],

        ]);

        if ($validator->fails()) {
            $response['status'] = 0;
            $response['msg'] = $validator->errors();
        } else {
            $datos = $request->getContent();
            $datos = json_decode($datos);

            $cardName = Cards::where('card_name', 'like', '%' .$request->input('card_name'). '%')->first();

            //$cardID = $cardName['id'];

            $userToken = User::where('token', $datos->token_val)->first();

            if($cardName){
                //$response['id']=$cardID;
                $venta = new CardsOnSale();
                $venta->price = $datos->price;
                $venta->quantity = $datos->quantity;

                $venta->card_id = $datos->card_id;
                $venta->user_id = $userToken->id;

                try{
                    $venta->save();
                    $response['msg'] = "Carta o Cartas en venta con el ID de VENTA: ".$venta->id;
                }catch(\Exception $e){
                $response['msg'] = "Se ha producido un error: ".$e->getMessage();
                }
            }else{
                $response['status'] = 0;
                $response['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
        }
        return response()->json($response);
    }

    public function cartasVentaBusqueda(Request $request)
    {
        $response = ["status" => 1, "msg" => ""];
        $validator = Validator::make(json_decode($request->getContent(), true), [
            'busqueda' => ['required', 'max:100']
        ]);

        if ($validator->fails()) {
            $response['status'] = 0;
            $response['msg'] = $validator->errors();
        }else{
            $datos = $request->getContent();
            $datos = json_decode($datos);

            try {

                $busqueda = DB::table('cards_onsale')
                ->join('users', 'users.id', '=', 'cards_onsale.user_id')
                ->join('cards', 'cards.id', '=', 'cards_onsale.card_id')
                ->select('cards.card_name', 'cards_onsale.quantity', 'cards_onsale.price', 'users.name as user'  )
                ->where('cards.card_name','like','%'. $datos->busqueda.'%')
                ->orderBy('cards_onsale.price','ASC')
                ->get();
                $response['msg'] = $busqueda;
            } catch (\Exception $e) {
                $response['status'] = 0;
                $response['msg'] = 'Se ha producido un error: '.$e->getMessage();
            }
        }
        return response()->json($response);
    }

    public function subirCartasMagicUrl(Request $request)
    {
        $response = ["status" => 1, "msg" => ""];
        $dataUrl = Http::acceptJson()->get('https://api.magicthegathering.io/v1/cards');
        $data = json_decode(file_get_contents(storage_path()."\magic.json"),true);

        $datos = $request->getContent();
        $datos = json_decode($datos);

        $arrayData= $data['cards'];
        $array2=$arrayData[101];
        print_r($array2['name']);
        print_r($array2['type']);
        print_r($array2['imageUrl']);

        //$name = DB::table($data)->select('cards')->where('name')->get();


        $userToken = User::where('token', $datos->token_val)->first();
        $collectionMagic = "magic";
        $imageDefault = "default";

        //$response['msg'] = $data->cards[0]->name;



        if($data){
            $card = new Cards();
            $card->card_name = $array2['name'];
            $card->description = $array2['text'];
            $card->collection = $collectionMagic;
            $card->user_id = $userToken->id;
            $card->card_price = $datos->card_price;
            $card->edition = $array2['setName'];
            $card->imageUrl =  $array2['imageUrl'];
            //$card->imageUrl =  $imageDefault;
                try{
                    $card->save();
                    $response['msg'] = "Carta guardada con id ".$card->id;
                }catch (Exception $e){
                $response['msg2'] = "Se ha producido un error: ".$e->getMessage();
            }
        }else{
            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error: ".$e->getMessage();
        }
        return response()->json($response);
    }

}
