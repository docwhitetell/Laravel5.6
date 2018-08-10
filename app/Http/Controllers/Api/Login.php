<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client;
use Laravel\Passport\Token;
use Illuminate\Support\Facades\DB;
// use Symfony\Component\HttpFoundation\Cookie;

class Login extends Controller
{
    protected $username = "mobile";
    /*处理 api登录 */
    public function login(Request $request){
        $http = new GuzzleHttp\Client;
        $mobile = $request->get('mobile');
        $password = $request->get('password');
        $passwordClient=Client::find(2);
        if (Auth::attempt(['mobile' => $mobile, 'password' => $password])) {
            // 认证通过...
/*            $this->cleanExpiresAccessToken();
            $this->cleanExpiresRefreshToken();*/
            $scopes='*';
            $response = $http->post(env('APP_URL').'/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $passwordClient->id,
                    'client_secret' => $passwordClient->secret,
                    'mobile' => $mobile,
                    'password' =>  $password,
                    'scope' => $scopes,
                ],
            ]);
            $data=json_decode((string) $response->getBody(), true);
            return response()->json(['error'=>false,'token'=>$data]);
        }else{
            return response()->json(['error'=>'Mobile Number or Password not Match!'],200);
        }

    }
    /*处理 api登录 */

    /* 带上refresh_token 刷新 access_token      */
    public function refresh(Request $request){
        $refreshToken=$request->get('refresh');
        $http = new GuzzleHttp\Client;
        $passwordClient=Client::find(2);
        $user=$request->get('user');
        if($user['email']==='example@react.com'){
            $scopes='';
        }else{
            $scopes='*';
        }
        $response = $http->post(env('APP_URL').'/oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => 2,
                //请替换为你自己的client_secret
                'client_secret' =>$passwordClient->secret,
                'scope' => $scopes,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
    /* 带上refresh_token 刷新 access_token */

    /* 清除数据库过期access_token */
    public function cleanExpiresAccessToken(){
        $nowtime=date('Y-m-d H:i:s',time());
        $expiresTokens=Token::where('expires_at','<',$nowtime)->delete();
    }
    /* 清除数据库过期refresh_token */
    public function cleanExpiresRefreshToken(){
        $nowtime=date('Y-m-d H:i:s',time());
        $expiresTokens = DB::table('oauth_refresh_tokens')->where('expires_at','<',$nowtime)->delete();
    }


}
