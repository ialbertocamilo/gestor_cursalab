<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Massive\UserMassive;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Auth\LoginController;

class Integrations extends Model
{
    protected function updateCreateUsers($users){
        $user_massive = new UserMassive();
        $users_collect = collect();
        $static_headers = $user_massive->getStaticHeaders();
        $criteria = Criterion::select('id as criterion_id','name','code')->where('active',1)->orderBy('position')->get();
        $static_header_api = $static_headers->pluck('code');
        $users_collect->push($static_headers->pluck('header_name')->merge($criteria->pluck('name')));
        foreach ($users as $user) {
            $temp_user = [];
            foreach ($static_header_api as $static_header) {
                $temp_user[] = isset($user[$static_header]) ? $user[$static_header] : '' ;
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
            // 'updated_users' =>$us->q_updates,
            // 'amount_errors' => $us->q_errors,
            'processed_data' => $user_massive->processed_users,
            'errors' =>  $user_massive->errores
        ];
        return  ['data'=>$data,'code'=>200];
    }
    protected function getCriteria(){
        $criteria = Criterion::select('id as criterion_id','name','code as criterion_code')->where('active',1)->orderBy('position')->get();
        return  ['data'=>['criteria'=>$criteria],'code'=>200];
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
}
