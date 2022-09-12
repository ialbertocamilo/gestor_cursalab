<?php

namespace App\Http\Controllers\IntegrationRest;

use App\Models\Integrations;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUpdateUserRequest;

class IntegrationsController extends Controller
{
    public function updateCreateUsers(CreateUpdateUserRequest $request){
        // try {
            $users = $request->get('users'); 
            $response = Integrations::updateCreateUsers($users);
            return response()->json($response['data'], $response['code'] ? $response['code'] : 500);
        // } catch (\Throwable $th) {
        //     return response()->json(
        //         ['message'=>'Server error.']
        //     ,500);
        // }
    }
    public function getCriteria(){
        try {
            $response = Integrations::getCriteria();
            return response()->json($response['data'], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function getValuesCriterion($criterion_id){
        // try {
            $response = Integrations::getValuesCriterion($criterion_id);
            return response()->json($response['data'], $response['code'] ? $response['code'] : 500);
        // } catch (\Throwable $th) {
        //     return response()->json(
        //         ['message'=>'Server error.']
        //     ,500);
        // }
    }
    public function authUser(AuthRequest $request){
        try {
            $response = Integrations::authUser($request);
            return response()->json($response['data'], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function getSecretKey(AuthRequest $request){
        try {
            $response = Integrations::getSecretKey($request);
            return response()->json($response['data'], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
}
