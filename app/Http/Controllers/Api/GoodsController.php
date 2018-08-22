<?php

namespace App\Http\Controllers\Api;

use Encore\Admin\Grid\Filter\In;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Message as ApiMsg;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Goods;
use App\Models\Shop;
use \Exception;
use phpDocumentor\Reflection\Types\Integer;
use function Psy\sh;

class GoodsController extends Controller
{
    use ApiMsg;

    /*
    * 商铺的商品列表 */
    /**
     * @SWG\Get(path="/api/shop/{shop_id}/goods", tags={"Goods 商品类Api"},summary="商铺商品列表",description="商铺商品列表",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="shop_id",type="number",description="商店id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function index(Request $request, $shopId){
        $shop = Shop::find($shopId);
        if(!$shop){return $this->sendErrorMsg('商店不存在！');}

        return $this->sendSuccessMsg('', $shop->goods);
    }
    /*
    * 增加商品*/
    /**
     * @SWG\Get(path="/api/shop/{shop_id}/goods/{goods_id}", tags={"Goods 商品类Api"},summary="商品详情",description="商品详情",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="shop_id",type="number",description="商店id",required=true),
     *   @SWG\Parameter(in="path",name="goods_id",type="number",description="商品id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function detail(Request $request, $shop_id, $goodsId){
        $data = Goods::find($goodsId);
        return $this->sendSuccessMsg('',$data);
    }

    /*
     * 增加商品*/
    /**
     * @SWG\Post(path="/api/shop/{shop_id}/goods", tags={"Goods 商品类Api"},summary="添加商品",description="添加商品",operationId="",produces={"application/json"},
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
    public function create(Request $request, $shopId){
        $shop = Shop::find($shopId);
        if(!$shop){return $this->sendErrorMsg('商店不存在！');}

        if(Shop::find($shopId)->user_id !== $request->user()->id){
           return $this->sendErrorMsg('非法操作！这不是您的商铺！');
        }

        $data = $request->all();
        $data['shop_id']=$shopId;
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
     * 更新商品 */
    /**
     * @SWG\Put(path="/api/shop/{shop_id}/goods", tags={"Goods 商品类Api"},summary="更新商品",description="更新商品",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",description="Content-Type",default="application/x-www-form-urlencoded"),
     *   @SWG\Parameter(in="path",name="id",type="number",description="商店id",required=true),
     *   @SWG\Parameter(in="formData",name="name",type="string",description="商品名称",required=true),
     *   @SWG\Parameter(in="formData",name="main_pic",type="string",description="商品主图",required=true),
     *   @SWG\Parameter(in="formData",name="media",type="string",description="商品大图",required=false),
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
    public function update(Request $request, $shopId, $goodId){
        // return ['user_id'=>$request->user()->id,'shop_id'=>(Integer)$shopId, 'goodsId'=>(Integer)$goodId];
        if($this->isIllegal($request->user(),$shopId, $goodId)){
            return $this->sendErrorMsg('非法操作！这不是您的商铺或商品！');
        }
        $goods = Goods::find($goodId);
        if(!$goods){return $this->sendErrorMsg('该商品不存在！');}

        $data = $request->all();
        $validator = $this->goodsValidator($data);
        if($validator->fails()){
            return $this->sendErrorMsg($validator->errors());
        }
        try{
            $goods->update($data);
            return $this->sendSuccessMsg('更新成功！');
        }catch(\Exception $e){
            return $this->sendSqlErrorMsg($e);
        }
    }
    /*
    * 删除商品*/
    /**
     * @SWG\Delete(path="/api/shop/{shop_id}/goods/{goods_id}", tags={"Goods 商品类Api"},summary="删除商品",description="删除商品",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="path",name="shop_id",type="number",description="商店id",required=true),
     *   @SWG\Parameter(in="path",name="goods_id",type="number",description="商店id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function delete(Request $request, $shopId, $goodsId){
        if($this->isIllegal($request->user(),$shopId, $goodsId)){
            return $this->sendErrorMsg('非法操作！这不是您的商铺或商品！');
        }

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

    public function isIllegal($user, $shopId, $goodsId) {
        $shop = Shop::find($shopId);
        $goods = Goods::find($goodsId);
        if(!$shop || !$goods){return true;}
        if($shop->user_id === (Integer)$user->id && $goods->shop_id === (Integer)$shopId){
            return false;
        }
        else{
            return true;
        }
    }
    public function isShopExist($shopId){
        return !!Shop::find($shopId);
    }
}
