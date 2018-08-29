<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Message as ApiMsg;
use App\Http\Controllers\Controller;
use App\Mail\OrderShipped;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use \Exception;


class UserController extends Controller
{
    use ApiMsg;
    /* 获得 Email 验证码  */
    private $code_length = 6;

    /**
     * @SWG\Get(path="/api/user/bindEmail", tags={"User"},summary="获得邮箱验证码",description="邮箱验证码",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",required=true,default="application/json"),
     *   @SWG\Parameter(in="path",name="email",type="string",description="邮箱",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function getEmailVerifyCode(Request $request){
        $email = $request->get('email');
        if(!$email){return ['message'=>'邮箱地址不应为空！', 'error' => true ,'status' => 500];}
        $user = $request->user();
        $code = str_pad(random_int(1, 999999), $this->code_length, 0, STR_PAD_LEFT);
        try{
            Mail::to($request->get('email'))->send(new OrderShipped('emails.EmailCode', ['code'=>$code]));
        }catch(Exception $e){
            return ['message'=>'验证码发送失败！', 'error' => $e->getMessage(), 'status' => 500];
        }
        $key = 'EmailVerificationCode_' . str_random(15);
        $expiredAt = now()->addMinutes(10);
        // 缓存验证码 10 分钟过期。
        Cache::put($key, ['email' => $email, 'code'=> $code, 'user_id' => $user->id], $expiredAt);
        return ['message'=>'验证码已发生到您的邮箱！请查收！', 'error' => false, 'code' => $code, 'key'=> $key, 'status' => 200];
    }

    /**
     * @SWG\Post(path="/api/user/bindEmail", tags={"User"},summary="绑定邮箱",description="绑定邮箱",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",required=true,default="application/json"),
     *   @SWG\Parameter(in="formData",name="code",type="string",description="邮箱验证码",required=true),
     *   @SWG\Parameter(in="formData",name="email",type="string",description="邮箱地址",required=true),
     *   @SWG\Parameter(in="formData",name="key",type="string",description="上一步获取邮箱验证码返回的key",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function bindEmail(Request $request){
        $data = $request->all();
        $user = $request->user();
        $validator = Validator::make(
            $data,
            [
            'email' => 'required|email|unique:users',
            'code' => 'required|size:6',
            'key' => 'required|string',
        ],
            [
            "email.required"=>'Email地址不能为空！',
            'code.required'=>'验证码不能为空！',
            'code.size'=> '验证码长度必须为'.$this->code_length,
            'key.required'=>'key不能为空！'
        ]);
        if($validator->fails()){
            return ['error'=> true, 'message'=> $validator->errors(), 'data'=> $data, 'status' => 500];
        }

        $cache = Cache::get($data['key']);
        if(!$cache){
            return ['error'=> true, 'message'=> '验证码已过期','status' => 500];
        }

        if (!hash_equals($cache['code'], $data['code']) || !hash_equals($cache['email'], $data['email'])){
            // 请求体中的 verification_code （手机验证码）是否与缓存中的验证码匹配
            return [
                'error' => true,
                'message' =>'邮箱或验证码错误!',
                'status' => 500,
                'emailEqual' => hash_equals($cache['email'], $data['email']),
                'codeEqual' => hash_equals($cache['code'], $data['code']),
            ];
        }

        $user->email = $data['email'];
        if($user->save()){
            Cache::forget($data['key']);
            return ['error'=> false, 'status' => 200 , 'message'=> '邮箱绑定成功'];
        };
    }

    /**
     * @SWG\Put(path="/api/user", tags={"User"},summary="更新用户信息",description="更新",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",required=true,default="application/json"),
     *   @SWG\Parameter(in="formData",name="name",type="string",description="新的用户昵称",required=true),
     *   @SWG\Parameter(in="formData",name="avatar",type="number",description="用户头像",required=false),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function update(Request $request){
        $name = $request->get('name');
        $avatar = $request->get('avatar') ? $request->get('avatar'):'default_avatar.png';
        $data = ['name'=>$name, 'avatar'=>$avatar];
        $user = $request->user();
        $validator = Validator::make($data, [
            'name'=> 'required',
        ]);

        if($validator->fails()){
            return ['error'=>true,'status'=>500, 'message'=> $validator->errors(), 'data'=>$request->all()];
        }
        $user->name = $name;
        $user->avatar = $avatar;
        try{
            $user->save();
            return ['error'=>false,'status'=>200, 'message'=>'用户信息更新成功'];
        }catch(Exception $e){
            return ['error'=>true,'status'=>500, 'message'=>$e->getMessage()];
        }
    }


    public function detail(Request $request){
        return $this->sendSuccessMsg('',$request->user());
    }
}
