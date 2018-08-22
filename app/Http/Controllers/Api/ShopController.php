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
use function Psy\sh;

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
        $validator = $this->shopValidator($data);
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
        $validator = $this->shopValidator($data, $id);
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
     * 店铺详情*/
    /**
     * @SWG\Get(path="/api/shop/{id}", tags={"Shop"},summary="商店详情",description="商店详情",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="id",type="string",description="id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function detail(Request $request, $id){
       $shop = Shop::find($id);
       $shop['goods'] = $shop->goods;
       return $shop;
    }

    /*
   *  我的店铺审核 */
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

    public function shopValidator(array $data, $id=null){
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

    /*
    * 增加商品*/
    /**
     * @SWG\Post(path="/api/shop/{shop_id}/goods", tags={"Shop"},summary="创建我的商店",description="创建商店",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="id",type="number",description="商店id",required=true),
     *   @SWG\Parameter(in="formData",name="name",type="string",description="商品名称",required=true),
     *   @SWG\Parameter(in="formData",name="main_pic",type="string",description="商品主图",required=true),
     *   @SWG\Parameter(in="formData",name="big_pic",type="string",description="商品大图",required=false),
     *   @SWG\Parameter(in="formData",name="description",type="string",description="商品描述",required=false),
     *   @SWG\Parameter(in="formData",name="content",type="string",description="商品详情",required=false),
     *   @SWG\Parameter(in="formData",name="status",type="string",description="商品状态",required=true, default="正常"),
     *   @SWG\Parameter(in="formData",name="type",type="string",description="商品类型",required=true),
     *   @SWG\Parameter(in="formData",name="tag",type="string",description="商品标签",required=false),
     *   @SWG\Parameter(in="formData",name="price",type="string",description="商品价格",required=true),
     *   @SWG\Parameter(in="formData",name="stock",type="string",description="商品库存",required=true),
     *   @SWG\Parameter(in="formData",name="discount_price",type="string",description="商品折扣价格",required=false),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function createGoods(Request $request, $shopId){
        $shop = Shop::find($shopId);
        $data = $request->all();
        $validator = $this->goodsValidator($data);
        if($validator->fails()){
            return $this->sendErrorMsg($validator->errors());
        }
        try{
            $goods = new Goods($data);
            $goods->save();
            return $this->sendSuccessMsg('创建成功');
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }

    /*
    * 增加商品*/
    /**
     * @SWG\Delete(path="/api/shop/{shop_id}/goods/{goods_id}", tags={"Shop"},summary="创建我的商店",description="创建商店",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="shop_id",type="number",description="商店id",required=true),
     *   @SWG\Parameter(in="path",name="goods_id",type="number",description="商店id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function deleteGoods(Request $request, $shopId, $goodsId){
        $shop = Shop::find($shopId);
        if(!$shop){return $this->sendErrorMsg('商店不存在！');}
        $deleteGood = $shop->goods->where('id',$goodsId)->first();
        if($deleteGood){
            try{
                $deleteGood->delete();
                return $this->sendSuccessMsg('删除成功!');
            }catch(\Exception $e){
                return $this->sendSqlErrorMsg($e);
            }
        }else{
            return $this->sendErrorMsg('商品不存在！');
        }
    }

    public function goodsValidator(array $data){
        return Validator::make(
            $data,
            [
                'shop_id'=> 'required',
                'name' => 'required',
                'main_pic' => 'required',
                'big_pic' => 'nullable',
                'description'=>'nullable',
                'content'=>'nullable',
                'status'=>'required',
                'type'=>'required',
                'tag'=>'nullable',
                'price'=>'required|numeric|min:0.01|max:99999999.00',
                'stock'=>'required|numeric|min:1|max:99999999',
                'discount_price'=>'nullable',
            ],
            [
                "shop_id.required"=>'商店id不能为空！',
                "name.required"=>'商品名不能为空！',
                'main_pic.required'=>'商品主图不能为空！',
                'status.required'=> '商品状态不能为空！',
                'type'=> '商品类型不能为空！',
                'price.required'=> '商品价格不能为空！',
                'price.numeric'=> '商品价格不能为非数值！',
                'price.min'=> '商品价格不能为0！',
                'price.max'=> '商品价格不能为大于99999999！',
                'stock.numeric'=> '商品库存不能为非整数值类型的值！',
                'stock.required'=> '商品库存不能为空！',
                'stock.min'=> '商品库存不能小于1！',
                'stock.max'=> '商品库存不能超过99999999！'
            ]);
    }
}
