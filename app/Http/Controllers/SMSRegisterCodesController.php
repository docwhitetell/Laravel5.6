<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mockery\Exception;
use Overtrue\EasySms\EasySms;
use App\User;
use PhpParser\Error;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Api\Message;

class SMSRegisterCodesController extends Controller
{
    use Message;
    /**
     * @SWG\Get(path="/api/smsCode",tags={"Auth"},summary="获取手机注册验证码",description="获取验证码",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="query",name="mobile",type="string",description="手机号",required=true,),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function getSmsCode(Request $request, EasySms $easySms)
    {
        $mobile = $request->get('mobile');
        $response = null;
        if(!$mobile){
            return $this->sendErrorMsg('手机号为必填!');
        }
        // 生成4位随机数，左侧补0
        $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
        /*try {
            $result = $easySms->send($mobile, [
                'content' => "【余皓明】您的验证码是{$code}。如非本人操作，请忽略本短信"
            ]);
        } catch (\Exception $exception) {
            $response = $exception->getExceptions();
            return response()->json($response);
        }*/

        //生成一个不重复的key 用来搭配缓存cache判断是否过期
        $key = 'verificationCode_' . str_random(15);
        $expiredAt = now()->addMinutes(10);

        // 缓存验证码 10 分钟过期。
        Cache::put($key, ['mobile' => $mobile, 'code'=> $code], $expiredAt);
        $data = [
            'key' => $key,
            'code'=> $code,
            'expired_at' => $expiredAt->toDateTimeString()
        ];
        return $this->sendSuccessMsg('短信发送成功！验证码10分钟内有效！',$data );
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
        $validator = $this->UserValidator($request->all());
        if($validator->fails()){
            return $this->sendValidateErrorMsg($validator);
        }
        $verificationKey = $request->get('verification_key');
        $verificationCode = $request->get('verification_code');
        $response = null;
        if(!$verificationKey){  // 请求体中是否包含 verification_key
            return $this->sendErrorMsg('缺少短信验证码！');
        }
        if(!$verificationCode){  // 请求体中是否包含 verification_code （手机验证码）
            return $this->sendErrorMsg('缺少verification_key！');
        }

        $verifyData = Cache::get($verificationKey);

        if(!$verifyData) {  // 缓存中如果没有相应的值则验证码以失效
            return $this->sendErrorMsg('短信验证码已失效!');
        }

        if (!hash_equals($verifyData['code'], $verificationCode)){
            return $this->sendErrorMsg('短信验证码错误!');
        }
        if (!hash_equals($verifyData['mobile'], $request->get('mobile'))){
            return $this->sendErrorMsg('前后手机号不一致!');
        }
        try{
            User::create([
                'name'=> 'User'.str_random(10),
                'mobile' => $verifyData['mobile'],
                'password' => bcrypt($request->get('password')),
            ]);
            Cache::forget($request->get('verification_key'));
            return $this->sendSuccessMsg('注册成功');
        }catch(Exception $e){
            $msg = $e->getMessage();
            return $this->sendErrorMsg($msg);
        }
    }

    public function UserValidator(array $data)
    {
        return Validator::make(
            $data,
            [
                'mobile' => 'required|unique:users',
                'password' => 'required|min:6|max:24',
                'verification_code' => 'required',
                'verification_key' => 'required'
            ],
            [
                "mobile.required" => '手机号不能为空！',
                "mobile.unique" => '该号码已被注册！',
                "password.required" => "密码不得为空！并且长度大于等于6位，小于等于24位！",
                "password.min" => "密码长度不得少于6位！",
                "password.max" => "密码长度不得大于24位",
                "verification_code.required" => '请输入正确的验证码！',
                'verification_key.required' => 'Key值不存在！'
            ]);
    }


}
