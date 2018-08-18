<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resources;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use \Exception;

class UploadController extends Controller
{
    /**
     * @SWG\post(path="/api/upload", tags={"User"},summary="更新用户信息",description="更新",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="formData",name="file",type="file",description="用户头像",required=false),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if(!$file){
            return  ['error'=>true, 'message'=>'Filed: file is required!'];
        }
        $user=$request->user();
        $newName = md5(date("Y-m-d-h-s") . $file->getClientOriginalName()) . '.' . ($file->getClientOriginalExtension());
        $path = $file->storeAs($user->id.'/'.date('Y-m-d'), $newName, 'uploads');
        $resource = new Resources();
        $resource->user_id = $user->id;
        $resource->file_name = $newName;
        $resource->origin_name = $file->getClientOriginalName();
        $resource->file_size = $file->getSize();
        $resource->type = $file->getClientMimeType();
        $resource->ext = $file->getClientOriginalExtension();
        $resource->path = env('APP_URL'). $user->id . '/' . $path;
        $data['link'] = $resource->path;
        try{
            $resource->save();
            return ['error'=>false, 'message'=>'上传成功！', 'data' => $resource, 'status'=>200];
        }catch (Exception $e){
            return ['error'=>false, 'message'=>$e->getMessage(), 'status'=>500];
        }

    }
}
