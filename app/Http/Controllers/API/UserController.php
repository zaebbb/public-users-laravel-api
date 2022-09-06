<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return string
     */
    public function index()
    {
        $searchUsers = User::all();

        if(count($searchUsers) == 0){
            return response([
                "status" => false,
                "message" => "Похоже что ни одного пользователя еще не существует😥"
            ], 404)->setStatusCode(404, 'Users not found');
        }

        return response([
            'status' => false,
            'users' => [
                $searchUsers
            ]
        ], 200)->setStatusCode(200,'User found');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|object
     */
    public function store(Request $request)
    {
        $checkAuth = $request->header('authorization', $request->cookie("authorization"));

        if($checkAuth){
            return response([
                'status' => false,
                'message' => "Вы уже зарегистрированы на сайте"
            ], 403)->setStatusCode(403, 'You are already registered');
        }

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $resetPassword = $request->resetPassword;

        $errors = [];

//        search user
        $userSearchReal = User::where('name','=',"$name")->get();
        $emailSearchReal = User::where('email','=',"$email")->get();
        if(count($userSearchReal) !== 0){
            array_push($errors, array("name_unique" => "Выбранное вами имя пользовател уже занято"));
        }
        if(count($emailSearchReal) !== 0){
            array_push($errors, array("email_unique" => "Выбранноя вами почта уже занята"));
        }
//        validation
        if(empty($name)){
            array_push($errors, array("name_required" => "Поле логин обязательно к заполнению"));
        }
        if(strlen($name) < 5){
            array_push($errors, array("name_min" => "Логин не должен содержать менее 5 символов"));
        }
        if(empty($email)){
            array_push($errors, array("email_required" => "Поле почта обязательно к заполнению"));
        }
        if(empty($password)){
            array_push($errors, array("password_required" => "Поле пароль обязательно к заполнению"));
        }
        if(strlen($password) < 5){
            array_push($errors, array("password_min" => "Минимальная длина пароля должна быть более 5 символов"));
        }
        if(empty($resetPassword)){
            array_push($errors, array("passwordReset_required" => "Поле повторный пароль обязательно к заполнению"));
        }
        if($password !== $resetPassword){
            array_push($errors, array("password_error" => "Введенные пароли не одинаковы"));
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'error' => [
                    $errors
                ]
            ], 400)->setStatusCode(400, 'Errors Validated');
        }

        $createUser = User::create([
            'name' => "$name",
            'email' => "$email",
            'password' => md5($password),
            'bearerToken' => ""
        ]);

//        generate token
        $generateToken = Str::random(60) . $createUser->id;

        $createUser->update([
            'bearerToken' => "$generateToken"
        ]);

        return response([
            'status' => true,
            'user_id' => $createUser->id
        ], 201)
            ->setStatusCode(201, 'created succesful')
            ->header('authorization', "$generateToken")
            ->cookie("authorization", "$generateToken", 60 * 24);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $validCheck = $request->header('authorization', $request->cookie('authorization'));
        $userToken = User::where('bearerToken', '=',"$validCheck")->get();
        if(count($userToken) === 0){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'Access denied');
        }

        $idUser = User::find($id);

        if($idUser === null){
            return response([
                'status' => false,
                'message' => 'Пользователь не обнаружен'
            ], 404)->setStatusCode(404, 'User not found');
        }

        return response([
            'status' => true,
            'user' => [
                $idUser
            ]
        ], 200)->setStatusCode(200, 'user found')->header('authorization', "$validCheck");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response|string
     */
    public function update(Request $request, $id)
    {
        $tokenCheck = $request->header('authorization', $request->cookie('authorization'));
        $userToken = User::where('bearerToken', '=', "$tokenCheck")->get();
        if(count($userToken) === 0){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)
                ->setStatusCode(403, 'Access denied');
        }

        $idToken = intval(substr($tokenCheck, 60));

        $searchUser = User::find($idToken);

        if($searchUser->id === $id || $searchUser->name === 'admin'){
            $name = $request->name;
            $email = $request->email;
            $password = $request->password;
            $passwordReset = $request->passwordReset;

            $errors = [];

            if(empty($name)){
                $errors[] = array('name_required' => "Поле имя обязательно к заполнению");
            }
            if(strlen($name) < 5){
                $errors[] = array('name_min' => "Поле имя должно быть не менеее 5 символов");
            }
            if(empty($email)){
                $errors[] = array('email_required' => "Поле почта обязательно к заполнению");
            }
            if(empty($password)){
                $errors[] = array('password_required' => "Поле пароля обязательно к заполнению");
            }
            if(strlen($password) < 5){
                $errors[] = array('password_min' => "Поле пароля должно быть не менеее 5 символов");
            }
            if(empty($passwordReset)){
                $errors[] = array('passwordReset_required' => "Поле повторный пароль обязательно к заполнению");
            }
            if($password !== $passwordReset){
                $errors[] = array('password_error' => "Пароли не совпадают");
            }

            if(count($errors) !== 0){
                return response([
                    'status' => false,
                    'errors' => [
                        $errors
                    ]
                ], 400)->setStatusCode(400, 'errors validated');
            }

            $userSearchUp = User::find($id);
            $userSearchUp->update([
                'name' => "$name",
                'email' => "$email",
                'password' => md5($password),
            ]);

            return response([
                'status' => true,
                'user_up' => [
                    $userSearchUp
                ]
            ], 201)->setStatusCode(201, 'updated successful')->header('authorization', "$tokenCheck");
        }
        return response([
            'status' => false,
            'message' => 'Вы пытаетесь редактировать не ваш аккаунт или у вас недостаточно прав'
        ], 403)->setStatusCode(403, 'Access denied');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $tokenSearch = $request->header('authorization', $request->cookie('authorization'));
        $searchUser = User::where('bearerToken','=',"$tokenSearch")->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'Access denied');
        }

        $checkIdUser = intval(substr($tokenSearch, 60));
        $userId = User::find($checkIdUser);

        if($userId->id === $id || $userId->name === 'admin'){
            $userDel = User::find($id);
            $userDel->delete();

            return response([
                'status' => true,
                'message' => 'delete complete'
            ], 201)->setStatusCode(201, 'delete successful')->header('authorization', "$tokenSearch");
        }

        return response([
            'status' => false,
            'message' => 'Доступ запрещен'
        ], 403)->setStatusCode(403, 'Access denied');
    }
}
