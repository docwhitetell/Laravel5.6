<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Message as ApiMsg;
use App\Models\Goods;
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
     * @SWG\Get(path="/api/shop", tags={"Shop 商店类Api"},summary="我的商店",description="商店列表",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function index(Request $request){
        return ['error'=> true, 'status'=>200 , 'data'=> $request->user()->myShops];
    }

    public function shops(Request $request){
        $data = Shop::paginate(20);
        return $this->sendSuccessMsg('查询成功',$data);
    }
    /*
     * 增加我的商店*/
    /**
     * @SWG\Post(path="/api/shop", tags={"Shop 商店类Api"},summary="创建我的商店",description="创建商店",operationId="",produces={"application/json"},
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
        if(!$this->checkUserCertify($user)){
            return $this->sendErrorMsg('请先先进行用户实名认证！',null);
        }
        $validator = $this->shopValidator($data);
        if($validator->fails()){
            return $this->sendValidateErrorMsg($validator);
        }
        try{
            $data['user_id'] = $user->id;
            $shop = $this->create($data);
            return $this->sendSuccessMsg('创建成功！',$shop);
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }

    /**
     * @SWG\Put(path="/api/shop/{id}", tags={"Shop 商店类Api"},summary="更新商店信息",description="更新商店信息",operationId="",produces={"application/json"},
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
        if($this->isIllegal($request->user(),$id)){
            return $this->sendErrorMsg('这不是您的商店！非法操作！');
        }

        $prev = Shop::find($id);
        if(!$prev->editable){
            return $this->sendErrorMsg('当前不可编辑！');
        }
        $data = $request->all();
        $validator = $this->shopValidator($data, $id);
        if($validator->fails()){
            return $this->sendValidateErrorMsg($validator);
        }
        try{
            $prev->update($data);
            return $this->sendSuccessMsg('保存成功', $prev);
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }

    /*
     * 删除店铺 */
    /**
     * @SWG\Delete(path="/api/shop/{id}", tags={"Shop 商店类Api"},summary="删除商店",description="删除商店",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="id",type="string",description="id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function delete(Request $request, $id){
        if($this->isIllegal($request->user(),$id)){
            return $this->sendErrorMsg('这不是您的商店！非法操作！');
        }
        try{
            DB::transaction(function () use($id){
                Shop::find($id)->delete();
                Goods::where('shop_id',$id)->delete();
                ShopCertify::where('shop_id',$id)->delete();
            });
            return $this->sendSuccessMsg('删除成功！');
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }

    /*
     * 店铺详情*/
    /**
     * @SWG\Get(path="/api/shop/{id}", tags={"Shop 商店类Api"},summary="商店详情",description="商店详情",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="id",type="string",description="id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function detail(Request $request, $id){
       $shop = Shop::find($id);
       //$shop['goods'] = $shop->goods;
       return $shop;
    }

    /*
   *  我的店铺审核 */
    /**
     * @SWG\Get(path="/api/shop/certify", tags={"Shop 商店类Api"},summary="我的审核",description="我的审核",operationId="",produces={"application/json"},
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
     * @SWG\Get(path="/api/shop/certify/{id}", tags={"Shop 商店类Api"},summary="申请营业审批",description="申请营业审批",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="id",type="string",description="id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function certify(Request $request, $id){
        if($this->isIllegal($request->user(),$id)){
            return $this->sendErrorMsg('这不是您的商店！非法操作！');
        }
        $shop = Shop::find($id);
        $user = $request->user();
        /* 实名认证检测 */
        if(!$user->myCertify || !$user->myCertify->certificated){
            return $this->sendErrorMsg('用户暂未通过实名审核！');
        }
        /* 审核检测 */
        if($shop->certify === '正在审核'){
            return $this->sendErrorMsg('正在审核！请务重复操作!');
        }else if($shop->certify === '通过审核'){
            return $this->sendErrorMsg('已完成审核！请务要重复操作!');
        }else{
            $certify = new ShopCertify();
            $certify->owner=$user->id;
            $certify->shop_id = $id;
            $shop->certify = '正在审核';
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
    }
    /*
     *  取消营业审批 */
    /**
     * @SWG\Delete(path="/api/shop/certify/{id}", tags={"Shop 商店类Api"},summary="删除营业审批申请",description="删除营业审批申请",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="id",type="string",description="id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function uncertify(Request $request, $id){
        if($this->isIllegal($request->user(),$id)){
            return $this->sendErrorMsg('这不是您的商店！非法操作！');
        }
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

    public function create($data) {
        if(!$data) {return false;}
        return Shop::create($data);
    }

    public function shopValidator(array $data, $id=null){
        return Validator::make(
            $data,
            [
                'name' => $id ? ['required', Rule::unique('shops')->ignore($id)] : 'required|unique:shops',
                'logo' => 'required',
                'location' => 'required|string',
                'type'=>'required',
                //'description'=>'nullable',
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

    public function isIllegal($user, $shopId) {
        $shop = Shop::find($shopId);
        if(!$shop){return true; }
        if($shop->user_id === $user->id){
            return false;
        }
        else{
            return true;
        }
    }


    protected function checkUserCertify($user) {
        if(!$user){
            return false;
        }

        if(!$user->myCertify->first()){
            return false;
        }
        return $user->myCertify->first()->certificated;
    }

}
