<?php
namespace App\Http\Controllers\Api;


use Illuminate\Support\Facades\Validator;

trait Message
{
    function sendValidateErrorMsg($validator){
        return ['error'=>true, 'status'=>500, 'message'=>$validator->errors()];
    }

    function sendSqlErrorMsg($e){
        return ['error'=>true, 'status'=>500, 'message'=>$e->getMessage()];
    }

    function sendSuccessMsg($msg, $data=null, $status=200){
        return ['error'=>false, 'status'=>200, 'message'=>$msg, 'data'=>$data];
    }
    function sendErrorMsg($msg = '', $data=null, $status=500){
        return ['error'=>true, 'status'=>500, 'message'=>$msg, 'data'=>$data];
    }
}