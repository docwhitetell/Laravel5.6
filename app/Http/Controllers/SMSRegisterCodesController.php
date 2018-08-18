<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mockery\Exception;
use Overtrue\EasySms\EasySms;
use App\User;
use PhpParser\Error;
use Illuminate\Support\Facades\Cache;

class SMSRegisterCodesController extends Controller
{
    /**
     * @SWG\Get(path="/api/smsCode",tags={"Auth"},summary="获取手机注册验证码",description="获取验证码",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="path",name="mobile",type="string",description="手机号",required=true,),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function getSmsCode(Request $request, EasySms $easySms)
    {
        $mobile = $request->get('mobile');
        $response = null;
        if(!$mobile){
            $response = ['error'=> true, 'message' => '手机号为必填!', 'code' => 402 ];
            return $response;
        }
        // 生成4位随机数，左侧补0
        $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
        try {
            $result = $easySms->send($mobile, [
                'content' => "【余皓明】您的验证码是{$code}。如非本人操作，请忽略本短信"
            ]);
        } catch (Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $response = $exception->getExceptions();
            return response()->json($response);
        }

        //生成一个不重复的key 用来搭配缓存cache判断是否过期
        $key = 'verificationCode_' . str_random(15);
        $expiredAt = now()->addMinutes(10);

        // 缓存验证码 10 分钟过期。
        Cache::put($key, ['mobile' => $mobile, 'code'=> $code], $expiredAt);
        //dd(Cache::get($key));
        return response()->json([
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ], 201);
    }

    /**
     * @SWG\Post(path="/api/register",tags={"Auth"},summary="通过手机验证码注册用户",description="注册",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="formData",name="verification_key",type="string",description="获取验证码步骤中返回的key",required=true),
     *   @SWG\Parameter(in="formData",name="verification_code",type="string",description="手机验证码",required=true),
     *   @SWG\Parameter(in="formData",name="mobile",type="string",description="手机号",required=true),
     *   @SWG\Parameter(in="formData",name="password",type="string",description="用户密码",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function register(Request $request)
    {
        $verificationKey = $request->get('verification_key');
        $verificationCode = $request->get('verification_code');
        $response = null;
        if(!$verificationKey){  // 请求体中是否包含 verification_key
            $response = ['error' => true, 'message' =>'缺少短信验证码！', 'status' => 500];
            return $response;
        }
        if(!$verificationCode){  // 请求体中是否包含 verification_code （手机验证码）
            $response = ['error' => true, 'message' =>'缺少verification_key！', 'status' => 500];
            return $response;
        }

        $verifyData = Cache::get($verificationKey);
        if(!$verifyData) {  // 缓存中如果没有相应的值则验证码以失效
            return ['error' => true, 'message'=> '短信验证码已失效!', 'status' => 500];
        }

        if (!hash_equals($verifyData['code'], $verificationCode)){
            // 请求体中的 verification_code （手机验证码）是否与缓存中的验证码匹配
            return ['error' => true, 'message' =>'短信验证码错误!', 'status' => 500];
        }

        try{
            User::create([
                'name'=> 'User'.str_random(10),
                'mobile' => $verifyData['mobile'],
                'password' => bcrypt($request->get('password')),
            ]);
            Cache::forget($request->get('verification_key'));
            $response = ['error'=> false, 'message' => '注册成功！', 'status' => 200];
        }catch(Exception $e){
            $msg = $e->getMessage();
            $response = ['error'=> true, 'message' => $msg, 'status' => 500];
        }

        return $response;
    }
}
