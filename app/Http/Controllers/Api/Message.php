<?php
namespace App\Http\Controllers\Api;


use Illuminate\Support\Facades\Validator;

trait Message
{
    function sendValidateErrorMsg($validator){
        return ['error'=>true, 'data'=> null, 'message'=>$validator->errors()];
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