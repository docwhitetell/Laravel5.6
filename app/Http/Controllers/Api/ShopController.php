<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Message as ApiMsg;
use App\Models\Shop;
use App\Models\ShopCertify;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{

    use ApiMsg;
    /*
    * 我的商店列表 */
    /**
     * @SWG\Get(path="/api/shop", tags={"Shop"},summary="我的商店",description="商店列表",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function index(Request $request){
        return ['error'=> true, 'status'=>200 , 'data'=> $request->user()->myShops];
    }
    /*
     * 增加我的商店*/
    /**
     * @SWG\Post(path="/api/shop", tags={"Shop"},summary="创建我的商店",description="创建商店",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="formData",name="name",type="string",description="商店名称",required=true),
     *   @SWG\Parameter(in="formData",name="logo",type="string",description="用户头像",required=true),
     *   @SWG\Parameter(in="formData",name="location",type="string",description="店铺位置",required=true),
     *   @SWG\Parameter(in="formData",name="type",type="string",description="店铺类型",required=false),
     *   @SWG\Parameter(in="formData",name="description",type="string",description="商店描述",required=false),
     *   @SWG\Parameter(in="formData",name="open_at",type="string",description="开门营业时间",required=true),
     *   @SWG\Parameter(in="formData",name="close_at",type="string",description="关门打烊时间",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function add(Request $request){
        $user = $request->user();
        $data = $request->all();
        $validator = $this->validator($data);
        if($validator->fails()){
            return $this->sendValidateErrorMsg($validator);
        }
        try{
            $shop = $this->create($data,$user->id);
            return $this->sendSuccessMsg('创建成功！',$shop);
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }

    /**
     * @SWG\Put(path="/api/shop/{id}", tags={"Shop"},summary="更新商店信息",description="更新商店信息",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",description="Content-Type",default="application/x-www-form-urlencoded"),
     *   @SWG\Parameter(in="path",name="id",type="string",description="id",required=true),
     *   @SWG\Parameter(in="formData",name="name",type="string",description="商店名称",required=true),
     *   @SWG\Parameter(in="formData",name="logo",type="string",description="用户头像",required=true),
     *   @SWG\Parameter(in="formData",name="location",type="string",description="店铺位置",required=true),
     *   @SWG\Parameter(in="formData",name="type",type="string",description="店铺类型",required=false),
     *   @SWG\Parameter(in="formData",name="description",type="string",description="商店描述",required=false),
     *   @SWG\Parameter(in="formData",name="open_at",type="string",description="开门营业时间",required=true),
     *   @SWG\Parameter(in="formData",name="close_at",type="string",description="关门打烊时间",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    /*
     * 更新店铺信息*/
    public function update(Request $request, $id){
        $prev = Shop::find($id);
        if(!$prev->editable){
            return $this->sendErrorMsg('当前不可编辑！');
        }
        $data = $request->all();
        $validator = $this->validator($data, $id);
        if($validator->fails()){
            return $this->sendValidateErrorMsg($validator);
        }
        $prev->name = $data['name'];
        $prev->logo = $data['logo'];
        $prev->location = $data['location'];
        $prev->type = $data['type'];
        $prev->description = $data['description'];
        $prev->open_at = $data['open_at'];
        $prev->close_at = $data['close_at'];
        try{
            $prev->save();
            return $this->sendSuccessMsg('保存成功', $prev);
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }

    /*
     * 删除店铺 */
    /**
     * @SWG\Delete(path="/api/shop/{id}", tags={"Shop"},summary="删除商店",description="删除商店",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="id",type="string",description="id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    /*
     * 更新店铺信息*/
    public function delete(Request $request, $id){
        try{
            DB::transaction(function () use($id){
                Shop::find($id)->delete();
                ShopCertify::where('shop_id',$id)->delete();
            });
            return $this->sendSuccessMsg('删除成功！');
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }


    /*
   *  我的审核 */
    /**
     * @SWG\Get(path="/api/shop/certify", tags={"Shop"},summary="我的审核",description="我的审核",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function certificating(Request $request){
        $user = $request->user();
        $certificating = ShopCertify::where(['owner'=>$user->id, 'approve'=>'正在审核'])->select('shop_id')->get();
        $ids = array();
        foreach ($certificating as $c){
            array_push($ids, $c->shop_id);
        }
        $data = Shop::find($ids);
        return $this->sendSuccessMsg('', $data);
    }
    /*
    *  申请营业审批 */
    /**
     * @SWG\Get(path="/api/shop/certify/{id}", tags={"Shop"},summary="申请营业审批",description="申请营业审批",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="id",type="string",description="id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function certify(Request $request, $id){
        $shop = Shop::find($id);
        $user = $request->user();
        $certify = new ShopCertify();
        $certify->owner=$user->id;
        $certify->shop_id = $id;
        $shop->certify = '审核中';
        $shop->editable = false;
        try{
            DB::transaction(function ()use($shop,$certify){
                $shop->save();
                $certify->save();
            });
            return $this->sendSuccessMsg('申请成功!');
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }
    /*
     *  取消营业审批 */
    /**
     * @SWG\Delete(path="/api/shop/certify/{id}", tags={"Shop"},summary="删除营业审批申请",description="删除营业审批申请",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="id",type="string",description="id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function uncertify(Request $request, $id){
        $shop = Shop::find($id);
        $shop->certify = '未审核';
        $shop->editable = true;
        try{
            DB::transaction(function ()use($shop,$id){
                $shop->save();
                ShopCertify::where('shop_id', $id)->delete();
            });
            return $this->sendSuccessMsg('取消审核成功!');
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }

    public function create($data,$uId) {
        if(!$data|| !$uId) {return false;}
        return Shop::create([
            'user_id' => $uId,
            'name'=>$data['name'],
            'logo'=>$data['logo'],
            'location'=>$data['location'],
            'type'=>$data['location'],
            'description'=>$data['description'],
            'open_at'=>$data['open_at'],
            'close_at'=>$data['close_at']
        ]);
    }

    public function validator(array $data, $id=null){
        return Validator::make(
            $data,
            [
                'name' => $id ? ['required', Rule::unique('shops')->ignore($id)] : 'required|unique:shops',
                'logo' => 'required',
                'location' => 'required|string',
                'type'=>'nullable',
                'description'=>'nullable',
                'open_at'=>'required',
                'close_at'=>'required',
            ],
            [
                "name.required"=>'商店名不能为空！',
                "name.unique"=>'该名字已被注册！',
                'logo.required'=>'商店logo不能为空！',
                'location.required'=> '商店地址不能为空！',
                'open_at.required'=> '营业时间不能为空！',
                'close_at.required'=> '打烊时间不能为空！',
            ]);
    }
}
