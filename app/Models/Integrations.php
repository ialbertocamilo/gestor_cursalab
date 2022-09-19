<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Criterion;
use App\Models\Workspace;
use App\Models\CriterionValue;
use App\Models\Massive\UserMassive;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Massive\ChangeStateUserMassive;
use App\Http\Resources\UserIntegrationResource;
use App\Http\Resources\CourseIntegrationResource;
use App\Http\Resources\CourseProgressIntegrationResource;

class Integrations extends Model
{
    protected function progressUser($request){
        $users = User::with(['subworkspace:id,name,parent_id','subworkspace.parent:id,name'])
                    ->whereNotNull('subworkspace_id')->select('id','username','fullname','subworkspace_id')
                    ->paginate(500);
        UserIntegrationResource::collection($users);
        return  ['data'=>['users'=>$users],'code'=>200];
    }
    protected function getCourseProgress($request){
        $course = $request->course;
        $course->load('segments.values');
        $users_id_segmented = $course->usersSegmented($course->segments,'users_id');
        $segmented_users = User::whereIn('id',$users_id_segmented)->where('active',1)
        ->with(['summary_courses'=>function($q)use($course){
            $q->where('course_id',$course->id)->select('id','course_id','user_id','status_id','created_at');
        },'summary_courses.status:id,name'])->select('id','document','fullname')->paginate(500);
        CourseProgressIntegrationResource::collection($segmented_users);
        return  ['data'=>['segmented_users'=>$segmented_users],'code'=>200];

    }
    protected function getCourses($request){
        $courses = Course::with(['segments.values','type:id,name','schools:id,name'])
        ->select('id','type_id','name', 'duration', 'investment','created_at')
        ->withCount(['summaries'=>function ($q) {
            $q->whereRelation('user','active',ACTIVE)->where('passed', true);
        }])
        ->paginate(100);
        CourseIntegrationResource::collection($courses);
        return  ['data'=>['courses'=>$courses],'code'=>200];
    }
    protected function updateCreateUsers($users,$workspace_id){
        $user_massive = new UserMassive();
        $user_massive->current_workspace = Workspace::where('id',$workspace_id)->first();
        $users_collect = collect();
        $static_headers = $user_massive->getStaticHeaders();
        $criteria = Criterion::select('id as criterion_id','name','code')->where('active',1)->orderBy('position')->get();
        $static_header_api = $static_headers->pluck('code');
        $users_collect->push($static_headers->pluck('header_name')->merge($criteria->pluck('name')));
        foreach ($users as $user) {
            $temp_user = [];
            foreach ($static_header_api as $static_header) {
                $value_text = isset($user[$static_header]) ? $user[$static_header] : '';
                if($static_header=='active'){
                    $value_text = ($value_text==1) ? 'Active' : 'Inactive';
                }
                $temp_user[] = $value_text;
            }
            foreach ($criteria as $criterion) {
                $temp_user[] = isset($user['criterions'][$criterion->code]) ? $user['criterions'][$criterion->code] : '' ;
            }
            $users_collect->push($temp_user);
        }
        //Procesar data
        $user_massive->collection($users_collect);
        $data = [
            // 'inserted_users' =>$us->q_inserts,
            'insert_updated_users' =>$user_massive->processed_users,
            'amount_errors' => count($user_massive->errors),
            'processed_data' => count($users_collect)-1,
            'errors' =>  $user_massive->errors
        ];
        return  ['data'=>$data,'code'=>200];
    }
    protected function getCriteria(){
        $criteria = Criterion::select('id as criterion_id','name','code as criterion_code')->where('active',1)->orderBy('position')->get();
        return  ['data'=>['criteria'=>$criteria],'code'=>200];
    }
    protected function getWorkspaces(){
        $workspaces = Workspace::select('id as workspace_id','name')->where('active',1)->whereNull('parent_id')->get();
        return  ['data'=>['workspaces'=>$workspaces],'code'=>200];
    }
    protected function getValuesCriterion($criterion_id){
        $criterion = Criterion::where('id',$criterion_id)
        ->with('field_type:id,code')
        ->select('id', 'name','required','field_id')
        ->first();
        if(is_null($criterion)){
            return [
                'data'=>['message'=>'Resource not found.'],
                'code'=>404
            ];
        }
        $colum_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);
        $criterion_values = CriterionValue::select('id as criterion_value_id',$colum_name.' as name')->where('criterion_id',$criterion->id)->get();
        $response['count'] = count($criterion_values);
        $response['criterion_values'] = $criterion_values;
        $response['format_type'] = $criterion->field_type->code;
        return  ['data'=>[$response],'code'=>200];
    }
    protected function authUser($request){
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password]) ){
            return [
                'data'=>['message'=>'Wrong credentials.'],
                'code'=>401
            ];
        }
        $user = Auth::user();
        $user->tokens()->delete();
        $auth_token = auth()->user()->createToken('accessToken');
        $expiration = $auth_token->token;
        $expiration->expires_at = Carbon::now()->addWeeks(4);//example 1 week
        $expiration->save();
        // $user = auth('integrations')->user();
        $secret_key = $request->header('secretkey');
        if(is_null($secret_key) || $user->secret_key != $secret_key){
            return [
                'data'=>['message'=>'Invalid secret key.'],
                'code'=>401
            ];
        }
        return [
            'data' => [
                'access_token' => $auth_token->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => Carbon::parse($expiration->expires_at)->format('Y-m-d H:i:s'),
                'expires_in_format'=>'Y-m-d H:m:s'
            ],
            'code'=>200
        ];
    }

    protected function getSecretKey($request){
        if (!auth()->attempt([
            'email'=>$request->email,
            'password'=>$request->password
        ])){
            return [
                'data'=>['message'=>'Wrong credentials.'],
                'code'=>401
            ]; 
        }
        $user = Auth::user();
        $secretKey = ($user->secret_key) ?  $user->secret_key : 'This Administrator does not have an associated secretKey.';
        return [
            'data' => [
                'secretKey' => $secretKey,
            ],
            'code'=>200
        ];
    }
    protected function inactivateUsers($data){
        return $this->change_status_user($data,0,'quantity_inactivated');
    }
    protected function activateUsers($data){
        return $this->change_status_user($data,1,'quantity_activated');
    }
    private function change_status_user($data,$state_user_massive,$name_parameter){
        $verify_parameters = $this->parameters_allow( ['users_document','users_email'],$data);
        if($verify_parameters['code']!=200){
            return $verify_parameters;
        }
        $model_massive_state_user = new ChangeStateUserMassive();
        $static_headers = $model_massive_state_user->getStaticHeader();
        $users_dni = isset($data['users_document']) ?  collect($data['users_document']) : collect(); 
        $users_dni = $static_headers->merge($users_dni);
        $users_email = isset($data['users_email']) ?  collect($data['users_email']) : collect(); 
        $users_inactived = $users_dni->merge($users_email);
        //Procesar data
        $model_massive_state_user->state_user_massive = $state_user_massive;
        $model_massive_state_user->collection($users_inactived);
        $data = [
            $name_parameter =>$model_massive_state_user->q_change_status,
            'amount_errors' => $model_massive_state_user->q_errors,
            'processed_data' => $users_inactived->count(),
            'errors' =>  $model_massive_state_user->errors
        ];
        
        return  ['data'=>$data,'code'=>200];
    }
    private function parameters_allow($parameters_allow,$data){
        $error = false;
        $message = '';
        $code = 200;
        foreach ($data as $parameter_name => $value) {
            if(!in_array($parameter_name,$parameters_allow)){
                $error = true;
                $code = 400;
                $message = "The parameter '".$parameter_name."' not allowed.";
            }
        }
        return  ['data'=>['errors'=>$error,'message'=>$message],'code'=>$code];
    }
}
