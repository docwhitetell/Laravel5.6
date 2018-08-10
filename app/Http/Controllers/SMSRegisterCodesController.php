<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;
use App\User;
use PhpParser\Error;
use Illuminate\Support\Facades\Cache;

class SMSRegisterCodesController extends Controller
{
    // 这里验证就不写了。
    public function getSmsCode(Request $request, EasySms $easySms)
    {
        //获取前端ajax传过来的手机号
        //dd($request->get('mobile'));
        $mobile = $request->get('mobile');

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

    public function register(Request $request)
    {
        /*  pull（读取完成后删除）
         * 从缓存中读取 $verification_key 并清除 ，不存在则返回错误*/
        $verifyData = Cache::get($request->get('verification_key'));
        //如果数据不存在，说明验证码已经失效。
        if(!$verifyData) {
            return response()->json(['status' =>0, 'message'=> '短信验证码已失效', 'code' => 422], 422);
        }

        // 检验前端传过来的验证码是否和缓存中的一致
        if (!hash_equals($verifyData['code'], $request->verification_code)){
            return ['error' => true, 'message' =>'短信验证码错误', 'data' => null, 'code' => 422];
            //return redirect()->back()->with('warning', '短信验证码错误');
        }

        $user = User::create([
            'name'=> 'User'.str_random(10),
            'mobile' => $verifyData['mobile'],
            'password' => bcrypt($request->get('password')),
        ]);

        // 清除验证码缓存
        Cache::forget($request->verification_key);
        return ['error' => false, 'message' =>'短信验证码错误', 'data' => null, 'code' => 200];
        //return redirect()->route('login')->with('success', '注册成功！');
    }
}
