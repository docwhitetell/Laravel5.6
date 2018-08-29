<?php
namespace App\Http\Controllers\Api;


use Illuminate\Support\Facades\Validator;

trait Message
{
    function sendValidateErrorMsg($validator){
        $firstError = current($validator->errors());
        $errMsg = '';
        foreach($firstError as $key=>$value){
            $errMsg = current($value);
        }
        return ['error'=>true, 'data'=> null, 'message'=>$errMsg];
    }

    function sendSqlErrorMsg($e){
        return ['error'=>true, 'data'=> null, 'message'=>$e->getMessage()];
    }

    function sendSuccessMsg($msg, $data=null, $status=200){
        return ['error'=>false, 'message'=>$msg, 'data'=>$data];
    }
    function sendErrorMsg($msg = '', $data=null, $status=500){
        return ['error'=>true, 'message'=>$msg, 'data'=>$data];
    }
}