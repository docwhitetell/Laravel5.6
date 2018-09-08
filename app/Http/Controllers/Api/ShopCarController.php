<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Message as ApiMsg;
use App\Models\Goods;
use App\Models\Shop;
use App\Models\ShopCar;
use App\Models\ShopCertify;
use Encore\Admin\Form\Field\Number;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Exception;

class ShopCarController extends Controller
{

    use ApiMsg;
    /*
    * 我的购物车列表 */
    /**
     * @SWG\Get(path="/api/user/shopcar", tags={"User 用户"},summary="我的购物车（所有商铺）",description="我的购物车",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",description="Content_type",required=true, default="application/json"),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function index(Request $request){
        $user = $request->user();
        $shopCarsGroup = $user->myShopCars->groupBy('shop_id');
        $data = $this->getGoodsGroupByShop($shopCarsGroup);
        return $data;
    }

    /*
    * 以商店区分的购物车列表 */
    /**
     * @SWG\Get(path="/api/user/shopcar/{shop_id}", tags={"User 用户"},summary="关于id为shop_id的商铺，我的购物车",description="我的购物车",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",description="Content_type",required=true, default="application/json"),
     *   @SWG\Parameter(in="path",name="shop_id",type="number",description="商铺id",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function detail(Request $request, $shopId) {
        $user = $request->user();
        $cars = $user->myShopCars->where('shop_id', $shopId);
        $shop = Shop::find($shopId);
        $goodsList = $this->getGoodsListFromShopCar($cars);
        return $this->sendSuccessMsg('',['shop'=>$shop, 'goods'=>$goodsList]);
    }

    /*
    * 向购物车添加商品 */
    /**
     * @SWG\Post(path="/api/user/shopcar", tags={"User 用户"},summary="添加商品到购物车",description="添加到购物车",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",description="Content_type",required=true, default="application/json"),
     *   @SWG\Parameter(in="formData",name="shop_id",type="number",description="店铺id",required=true),
     *   @SWG\Parameter(in="formData",name="goods_id",type="number",description="商品id",required=true),
     *   @SWG\Parameter(in="formData",name="amount",type="number",description="商品数量",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function add(Request $request){
        $user = $request->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $validator = $this->validator($data);
        if($validator->fails()){
            return $this->sendValidateErrorMsg($validator);
        }
        if(count(Shop::find($data['shop_id'])->goods->where('id', $data['goods_id'])) === 0){
            return $this->sendErrorMsg('非法操作！该商店不含有该商品！');
        }
        /*
         * 检查购物车中是否已经存在同样的商品
         * */
        $prev = ShopCar::where(['user_id'=>$user->id, 'shop_id'=>$data['shop_id'], 'goods_id'=>$data['goods_id']])->get()->first();
        if($prev) {
            $prev['amount'] = $prev['amount'] + $data['amount'];
            $prev->save();
            return $this->sendSuccessMsg('添加成功！');
        }else{
            $shopCar = new ShopCar($data);
            $shopCar->save();
        }
        return $prev;
    }


    /*
    * 更新购物车 */
    /**
     * @SWG\Put(path="/api/user/shopcar", tags={"User 用户"},summary="添加商品到购物车",description="添加到购物车",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",description="Content_type",required=true, default="application/json"),
     *   @SWG\Parameter(in="formData",name="shop_id",type="number",description="店铺id",required=true),
     *   @SWG\Parameter(in="formData",name="goods_id",type="number",description="商品id",required=true),
     *   @SWG\Parameter(in="formData",name="amount",type="number",description="商品数量",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function update(Request $request){
        $user = $request->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $validator = $this->validator($data, false);
        if($validator->fails()){
            return $this->sendValidateErrorMsg($validator);
        }
        if(count(Shop::find($data['shop_id'])->goods->where('id', $data['goods_id'])) === 0){
            return $this->sendErrorMsg('非法操作！该商店不含有该商品！');
        }
        $query = ['shop_id'=>$request->get('shop_id'), 'user_id'=>$user->id,'goods_id'=>$request->goods_id];
        $shopCar = ShopCar::where($query)->get()->first();
        if((integer)$data['amount'] === 0){
            if($shopCar){
               $shopCar->delete();
                return $this->sendSuccessMsg('购物车更新成功！');
            }
            {
                return $this->sendErrorMsg('操作非法！没有该购物车记录！');
            }
        }
        if($shopCar){
            try{
                $shopCar->update($data);
                return $this->sendSuccessMsg('购物车更新成功！');
            }catch(Exception $e){
                return $this->sendSqlErrorMsg($e);
            }
        }
        else{
            $shopCar = new ShopCar($data);
            try{
                $shopCar->save();
                return $this->sendSuccessMsg('购物车更新成功！');
            }catch(Exception $e){
                return $this->sendSqlErrorMsg($e);
            }
        }
    }

    /*
    * 删除购物车记录 */
    /**
     * @SWG\Delete(path="/api/user/shopcar/{shop_car_id}", tags={"User 用户"},summary="添加商品到购物车",description="添加到购物车",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",description="Content_type",required=true, default="application/json"),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function delete(Request $request, $shopCarId){
        $shopCar = ShopCar::find($shopCarId);
        if(!$shopCar){
            return $this->sendErrorMsg('操作非法!该购物车记录不存在！');
        }
        if($shopCar->user_id !== $request->user()->id){
            return $this->sendErrorMsg('操作非法!这不是您的购物车！');
        }else{
            $shopCar->delete();
            return $this->sendSuccessMsg('删除成功！');
        }
    }

    /*
     * 返回与传入参数ShopCar列表， 对应的商品列表 */
    protected function getGoodsListFromShopCar($cars = array())
    {
        $goodsList = [];
        foreach ($cars as $car){
            array_push($goodsList, $car->goods);
        }
        return $goodsList;
    }

    /*
     * 以相同商店的商品为一组， 返回归档过的商品列表 */
    protected function getGoodsGroupByShop($carsGroups){
        $data = [];
        foreach ($carsGroups as $group){
            $shop = Shop::find($group->first()->shop_id);
            $goodsList = $this->getGoodsListFromShopCar($group);
            array_push($data, ['shop'=>$shop, 'goods'=>$goodsList]);
        }
        return $data;
    }

    protected function validator($data, $need_amount = true) {
        return Validator::make(
            $data,
            [
                'user_id' => 'required|exists:users,id',
                'shop_id'=> [
                    'required',
                    Rule::exists('shops','id')->where(function ($query){
                        $query->where(['certify'=>'通过审核']);
                    }),
                ],
                'goods_id'=>[
                    'required',
                    Rule::exists('goods','id')->where('status','正常')
                ],
                'amount'=>$need_amount ? 'required|integer|min:1' : 'required|integer|min:0'
            ],
            [
                'user_id.required'=>'请登录！',
                'user_id.exists' => '用户不存在！',
                'shop_id.required' => "商铺id不能为空！",
                'shop_id.exists' => '商店不存在或未营业！',
                'goods_id.required' => '商品id不能为空！',
                'goods_id.exists' => '商品不存在或已下架！',
                'amount.required' => '商品数量不能为空！',
                'amount.integer' => '商品数量必须为整数！',
                'amount.min' => '商品数量不得小于1！',
            ]);
    }
}
