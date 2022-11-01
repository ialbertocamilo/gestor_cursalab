<?php

namespace App\Http\Controllers\IntegrationRest;

use App\Models\Error;
use App\Models\Course;
use App\Models\Integrations;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StateUserRequest;
use App\Http\Requests\CreateUpdateUserRequest;

class IntegrationsController extends Controller
{
    public function updateCreateUsers(CreateUpdateUserRequest $request){
        try {
            $users = $request->get('users');
            $workspace_id = $request->get('workspace_id');
            $response = Integrations::updateCreateUsers($users,$workspace_id);
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            //Message in Slack
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function getCourses(Request $request){
        try {
            $response = Integrations::getCourses($request);
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function getCourseProgress(Course $course,Request $request){
        try {
            $request->course = $course;
            $response = Integrations::getCourseProgress($request);
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function progressUser(Request $request){
        try {
            $response = Integrations::progressUser($request);
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function listUsers(Request $request){
        // try {
            $response = Integrations::listUsers($request);
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        // } catch (\Throwable $th) {
        //     Error::storeAndNotificateException($th, request());
        //     return response()->json(
        //         ['message'=>'Server error.']
        //     ,500);
        // }
    }
    public function getSecretKey(AuthRequest $request){
        try {
            $response = Integrations::getSecretKey($request);
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function authUser(AuthRequest $request){
        try {
            $response = Integrations::authUser($request);
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function getCriteria(){
        try {
            $response = Integrations::getCriteria();
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function getValuesCriterion($criterion_id){
        try {
            $response = Integrations::getValuesCriterion($criterion_id);
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function getWorkspaces(){
        try {
            $response = Integrations::getWorkspaces();
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function inactivateUsers(StateUserRequest $request){
        try{
            $response = Integrations::inactivateUsers($request->all());
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
    public function activateUsers(StateUserRequest $request){
        try{
            $response = Integrations::activateUsers($request->all());
            return response()->json(['data'=>$response['data']], $response['code'] ? $response['code'] : 500);
        } catch (\Throwable $th) {
            Error::storeAndNotificateException($th, request());
            return response()->json(
                ['message'=>'Server error.']
            ,500);
        }
    }
}
