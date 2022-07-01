<?php

namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\LoginRequest;
use App\Models\Account;

class AuthenticationController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if ( Auth::attempt(['email' => $data['email'], 'password' => $data['password']]) )
        {
            $user = Auth::user();
            $user->tokens()->delete();
            $token = $user->createToken('accessToken')->accessToken;

            $user->load('gender', 'job_position', 'type', 'area', 'document_type', 'country');
            
            $user->getFirstMediaToForm('avatar', 'avatar', 'main', 'default_user.png');

            // $accounts_id = $user->getAccountsId();

            // $user->_accounts = Account::getDataWithMediaForSelect('logo', ['id', 'name', 'description'], $accounts_id);

            $response['user'] = $user;
            $response['token'] = $token;
            $response['roles'] = $user->getRoles();
            $response['permissions'] = $user->getPermissionsForApp();

            $name = $user->alias ?? $user->name;
            
            return $this->success($response, "Hola, {$name} ¡Bienvenido!");
        }

        return $this->error('Credenciales incorrectas');
    }

    public function logout(Request $request)
    {
        $user = auth()->user();

        $user->tokens()->delete();

        return $this->success([], '¡Adiós! ¡Vuelve pronto!');
    }

    public function me(Request $request)
    {
        $user = auth()->user();

        $user->load('gender', 'job_position', 'type', 'area', 'document_type', 'country');

        $user->getFirstMediaToForm('avatar', 'avatar', 'main', 'default_user.png');

        // $accounts_id = $user->getAccountsId();

        // $user->_accounts = Account::getDataWithMediaForSelect('logo', ['id', 'name', 'description'], $accounts_id);

        $user->makeHidden(['slug', 'media', 'gender_id', 'job_position_id', 'country_id', 'type_id', 'area_id', 'document_type_id', 'created_at', 'updated_at', 'deleted_at']);

        return $this->success($user);
    }

    public function profile(Request $request)
    {
        $user = auth()->user();

        // $user->load('gender', 'job_position', 'type', 'area', 'document_type', 'country');

        $user->getFirstMediaToForm('avatar', 'avatar', 'main', 'default_user.png');

        // $user->makeHidden(['slug', 'media', 'gender_id', 'job_position_id', 'country_id', 'type_id', 'area_id', 'document_type_id', 'created_at', 'updated_at', 'deleted_at']);

        $data = [
            'alias' => $user->alias,
            'quote' => $user->quote,
            'description' => $user->description,
            'avatar' => $user->avatar,
            'phone' => $user->phone,
            'telephone' => $user->telephone,
        ];

        return $this->success($data);
    }

    public function test(Request $request)
    {
        $user = bcrypt('Cursalab2022Test');

        return $this->success($user);
    }

    public function updateToken(Request $request)
    {
        try{
            $request->user()->update(['fcm_token' => $request->token]);

            return response()->json([
                'success'=>true
            ]);
            
        }catch(\Exception $e){
            report($e);
            return response()->json([
                'success'=>false
            ],500);
        }
    }

}
