<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|object|void
     */
    public function index(Request $request)
    {
        $tokenSearch = $request->header('authorization', $request->cookie('authorization'));
        $searchUser = User::where('bearerToken', '=', "$tokenSearch")->get();
        if(count($searchUser) !== 0){
            return response([
                'status' => false,
                'message' => 'Вы уже авторизованы'
            ], 403)->setStatusCode(403, 'You are logged');
        }

        $name = $request->name;
        $password = md5($request->password);

        $errors = [];

        if(empty($name)){
            $errors[] = array('name_required' => 'Поле имя обязательно для заполнения');
        }
        if(empty($password)){
            $errors[] = array('password_required' => 'Поле пароль обязательно для заполнения');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => [
                    $errors
                ]
            ], 400)->setStatusCode(400, 'errors validated');
        }

        $userCheckData = User::where('name','=',"$name")->get();
        if(count($userCheckData) === 0){
            return response([
                'status' => false,
                'message' => 'Неверный логин или пароль'
            ], 400)->setStatusCode(400, 'error login');
        }
        $userPassword = $userCheckData[0]::where('password','=',"$password")->get();
        if(count($userPassword) === 0){
            return response([
                'status' => false,
                'message' => 'Неверный логин или пароль'
            ], 400)->setStatusCode(400, 'error login');
        }

        $generateToken = Str::random(60) . $userPassword[0]->id;
        $userPassword[0]->update([
            'bearerToken' => "$generateToken"
        ]);

        return response([
            'status' => true,
            "bearerToken" => "$generateToken"
        ], 201)
            ->setStatusCode(201, 'account open')
            ->header('authorization', "$generateToken")
            ->cookie('authorization', "$generateToken", 60 * 24);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tokenSearch = $request->header('authorization', $request->cookie('authorization'));
        $searchUser = User::where('bearerToken','=',"$tokenSearch")->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'Вы не авторизованы'
            ], 403)->setStatusCode(403, 'no authorized');
        }

        return response([
            'status' => true,
            'message' => 'Вы вышли из аккаунта'
        ], 200)->setStatusCode(200, 'You close acccount')->cookie('authorization', '', 0 * (60 * 7 * 24));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
