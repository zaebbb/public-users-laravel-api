<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Messages;
use App\Models\User;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tokenAuth = $request->header('authorization', $request->cookie('authorization'));
        $searchUser = User::where('bearerToken','=',"$tokenAuth")->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'access denied');
        }

        if($searchUser[0]->name !== 'admin'){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'access denied');
        }

        $allMessages = Messages::all();

        if(count($allMessages) === 0){
            return response([
                'status' => false,
                'messages' => 'Сообщения не обнаружены'
            ], 404)->setStatusCode(404, 'messages not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $tokenAuth = $request->header('authorization', $request->cookie('authorization'));
        $searchUser = User::where('bearerToken','=',"$tokenAuth")->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'access denied');
        }

        $idSender = intval(substr($tokenAuth, 60));
        $idRecipient = $id;

        $findRecipient = User::find($id);
        if($findRecipient === null){
            return response([
                'status' => false,
                'message' => 'Пользователь не обнаружен'
            ], 404)->setStatusCode(404, 'user not found');
        }

        $message = $request->message;
        if(empty($message)){
            return response([
                'status' => false,
                'error' => 'Поле сообщение обязательно к заполнению'
            ], 400)->setStatusCode(400, 'error valid');
        }
        if(strlen($message) < 5){
            return response([
                'status' => false,
                'error' => 'Поле сообщение обязательно должно быть более 5 символов'
            ], 400)->setStatusCode(400, 'error valid');
        }

        $messageCreate = Messages::create([
            'user_id_left' => "$idSender",
            'user_id_left_message' => "$message",
            'user_id_right' => "$idRecipient",
            'user_id_right_message' => ''
        ]);

        return response([
            'status' => true,
            'message_id' => $messageCreate->id
        ], 201)->setStatusCode(201, 'message create');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Messages  $messages
     * @return \Illuminate\Http\Response
     */
    public function show(Messages $messages, Request $request, $idSender, $idRecipient)
    {
        $tokenAuth = $request->header('authorization', $request->cookie('authorization'));
        $userSearch = User::where('bearerToken','=',"$tokenAuth")->get();
        if(count($userSearch) === 0){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'access denied');
        }

        if($userSearch[0]->id !== intval($idSender)){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'access denied');
        }

        $sender = Messages::where('user_id_left','=',"$idSender", 'AND', 'user_id_right', '=', "$idRecipient")->get();
        $recipient = Messages::where('user_id_left','=',"$idRecipient", 'AND', 'user_id_right', '=', "$idSender")->get();

        $messages = [];
        $messages[] = $sender;
        $messages[] = $recipient;
        asort($messages);
        return response([
            'status' => true,
            'messages' => $messages
        ], 200)->setStatusCode(200, 'messages successful');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Messages  $messages
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Messages $messages)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Messages  $messages
     * @return \Illuminate\Http\Response
     */
    public function destroy(Messages $messages)
    {
        //
    }
}
