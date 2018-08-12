<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderShipped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /*处理 api登录 */

    /**
     * @SWG\Put(path="/api/user/{id}",
     *   tags={"User"},
     *   summary="提交用户手机号和密码，返回access_token",
     *   description="登录",
     *   operationId="",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="formData",
     *     name="name",
     *     type="string",
     *     description="昵称",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="email",
     *     type="string",
     *     description="邮箱",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="password",
     *     type="string",
     *     description="密码",
     *     required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function bindUserEmail(Request $request,$id){
        $code = 5201314;
        $flag = Mail::to($request->get('email'))->send(new OrderShipped('emails.EmailCode', ['code'=>$code]));
        if($flag){
            return '发送邮件成功，请查收！';
        }else{
            return '发送邮件失败，请重试！';
        }
        return $request->user();
        $name = $request->get('name');
        $email = $request->get('email');
        $mobile = $request->get('mobile');
        $password = $request->get('password');

    }

}
