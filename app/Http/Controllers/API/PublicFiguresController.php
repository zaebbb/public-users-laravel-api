<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PublicFigures;
use App\Models\User;
use Illuminate\Http\Request;
use function Ramsey\Uuid\v4;

class PublicFiguresController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $allFigures = PublicFigures::all();

        if(count($allFigures) === 0){
            return response([
                'status' => false,
                'message' => "Данные публичных личностей не обнаружены"
            ], 404)->setStatusCode(404, 'Figures not found');
        }

        return response([
            'status' => true,
            'figures' => [
                $allFigures
            ]
        ], 200)->setStatusCode(200, 'Figures not found')->header('authorization', $request->cookie('authorization'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tokenAuth = $request->header('authorization', $request->cookie('authorization'));
        $idUser = User::find(intval(substr($tokenAuth, 60)));
        if($idUser !== null){
            if($idUser->name === 'admin'){
                $figureName = $request->figureName;
                $descr = $request->descr;
                $photoProfile = $request->hasFile('photoProfile');
                $job = $request->job;

                $errors = [];

                if(empty($figureName)){
                    $errors[] = array('name_required' => 'Поле имя обязательно к заполнению');
                }
                if(empty($descr)){
                    $errors[] = array('descr_required' => 'Поле описание обязательно к заполнению');
                }
                if(empty($job)){
                    $errors[] = array('job_required' => 'Поле работа обязательно к заполнению');
                }
                if($photoProfile){
                    if($request->file('photoProfile')->getSize() > 2 * 1024 * 1024){
                        $errors[] = array('image_size' => 'Изображжение не должно превышать более 2 мб');
                    }
                    if(
                        !strpos($request->file('photoProfile')->getClientOriginalName(), '.jpg') &&
                        !strpos($request->file('photoProfile')->getClientOriginalName(), '.png')
                    ){
                        $errors[] = array('image_type' => 'Изображение должно быть формата png, jpg');
                    }
                } else {
                    $errors[] = array('image_required' => 'Поле изображение обязательно к заполнению');
                }


                if(count($errors) !== 0){
                    return response([
                        'status' => false,
                        'errors' => $errors
                    ], 400)->setStatusCode(400, 'error validated');
                }

                $fileName = v4() . '.jpg';
                $moveFile = $request->file('photoProfile')->move(public_path('/images/'), $fileName);

                $createProfile = PublicFigures::create([
                    'figureName' => "$figureName",
                    'descr' => "$descr",
                    'photoProfile' => "$fileName",
                    'job' => "$job"
                ]);

                return response([
                    'status' => true,
                    'figure_id' => $createProfile->id
                ], 201)
                    ->setStatusCode(201, 'figure creately')
                    ->header('authorization', "$tokenAuth");
            }
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'access denied');
        }

        return response([
            'status' => false,
            'message' => 'Доступ запрещен'
        ], 403)->setStatusCode(403, 'access denied');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PublicFigures  $publicFigures
     * @return \Illuminate\Http\Response
     */
    public function show(PublicFigures $publicFigures, $id, Request $request)
    {
        $publicFigure = PublicFigures::find($id);
        if($publicFigure === null){
            return response([
                'status' => false,
                'message' => 'Figure not found'
            ], 404)->setStatusCode(404, 'Figure not found');
        }

        return response([
            'status' => true,
            'figure' => [
                $publicFigure
            ]
        ], 200)->setStatusCode(200, 'Figure found')->header('authorization', $request->cookie('authorization'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicFigures  $publicFigures
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicFigures $publicFigures, $id)
    {
        $tokenAuth = $request->header('authorization', $request->cookie('authorization'));
        $searchAuth = User::where('bearerToken', '=',"$tokenAuth")->get();
        if(count($searchAuth) === 0){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'access denied');
        }

        if($searchAuth[0]->name !== 'admin'){
            return response([
                'status' => false,
                'message' => 'Доступ запрещен'
            ], 403)->setStatusCode(403, 'access denied');
        }

        $searchUserAccess = PublicFigures::find($id);

        if($searchUserAccess === null){
            return response([
                'status' => false,
                'message' => 'Публичная личность не обнаружена'
            ], 404)->setStatusCode(404, 'figures not found');
        }

        $figureName = $request->figureName;
        $descr = $request->descr;
        $photoProfile = $request->hasFile('photoProfile');
        $job = $request->job;

        $errors = [];

        if(empty($figureName)){
            $errors[] = array('name_required' => 'Поле имя обязательно к заполнению');
        }
        if(empty($descr)){
            $errors[] = array('descr_required' => 'Поле описание обязательно к заполнению');
        }
        if(empty($job)){
            $errors[] = array('job_required' => 'Поле работа обязательно к заполнению');
        }
        if($photoProfile){
            if($request->file('photoProfile')->getSize() > 2 * 1024 * 1024){
                $errors[] = array('image_size' => 'Изображжение не должно превышать более 2 мб');
            }
            if(
                !strpos($request->file('photoProfile')->getClientOriginalName(), '.jpg') &&
                !strpos($request->file('photoProfile')->getClientOriginalName(), '.png')
            ){
                $errors[] = array('image_type' => 'Изображение должно быть формата png, jpg');
            }
        } else {
            $errors[] = array('image_required' => 'Поле изображение обязательно к заполнению');
        }


        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 400)->setStatusCode(400, 'error validated');
        }

        $fileName = v4() . '.jpg';
        $moveFile = $request->file('photoProfile')->move(public_path('/images/'), $fileName);

        $searchUserAccess->update([
            'figureName' => "$figureName",
            'descr' => "$descr",
            'photoProfile' => "$fileName",
            'job' => "$job"
        ]);

        return response([
            'status' => true,
            'figure' => $searchUserAccess
        ], 200)
            ->setStatusCode(200, 'updated successful')
            ->header('authorization', "$tokenAuth");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PublicFigures  $publicFigures
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicFigures $publicFigures, Request $request, $id)
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

        $figureId = PublicFigures::find($id);

        if($figureId === null){
            return response([
                'status' => false,
                'message' => 'Публичная личность не обнаружена'
            ], 404)->setStatusCode(404, 'figure not found');
        }

        $figureId->delete();

        return response([
            'status' => true,
            'message' => 'delete successful'
        ], 200)->setStatusCode(200, 'delete successful');
    }
}
