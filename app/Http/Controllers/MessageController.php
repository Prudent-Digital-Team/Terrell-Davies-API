<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Message;

Route::post('message/create','MessageController@store');
Route::post('message/reply','MessageController@reply');
Route::get('user/messages','MessageController@index');
Route::get('user/messages/count','MessageController@index');
Route::get('user/messages/read','MessageController@read');
Route::get('user/messages/read/count','MessageController@read');
Route::get('user/messages/unread/count','MessageController@unread');

class MessageController extends Controller
{

    public function index(){

        $user_id = auth('api')->user()->id;

        $messages = Message::where('sender')->orWhere('receiver')->get();

        return response()->json([
            'message' => 'Messages',
            'data'=>$messages
        ], 200);

    }

    public function create(Request $request){

        //validation

        $array = $request->validate();

        if(!$request->validated){

        }

        $this->store($array);

    }

    public function reply(Request $request){

        $this->store($array);
    }

    protected function store(Array $data = null)
    {
        if($data){
            Message::create($data);
        }
    }

    protected function sendNotification(){
        //create notification component
    }

    protected function readMessages(){
        $user_id
        return Message::where('')
    }

   
}